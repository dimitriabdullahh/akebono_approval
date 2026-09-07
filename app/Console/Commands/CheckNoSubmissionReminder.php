<?php

namespace App\Console\Commands;

use App\Jobs\NoSubmissionReminderJob;
use App\Models\DocumentRequest;
use App\Models\DocumentRequestReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckNoSubmissionReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:check-no-submission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check dan kirim reminder/escalation untuk supplier yang belum mengirim dokumen sama sekali';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔍 Checking for document requests that need reminder...');

        // 1. Check untuk request baru yang belum punya reminder record
        $this->checkNewRequests();

        // 2. Check untuk reminder yang sudah ready untuk dikirim
        $this->checkExistingReminders();

        // 3. Check untuk reminder yang perlu di-stop (ada dokumen yang dikirim)
        $this->checkStopConditions();

        $this->info('✅ Check completed!');

        return 0;
    }

    /**
     * Check request baru yang belum punya reminder record
     */
    private function checkNewRequests()
    {
        $initialDelay = config('reminder.initial_reminder_delay', 3);

        // Cari request yang:
        // 1. Dibuat lebih dari [initial_delay] menit yang lalu
        // 2. Belum punya reminder record
        // 3. Belum ada dokumen yang dikirim
        $newRequests = DocumentRequest::whereDoesntHave('reminder')
            ->where('created_at', '<=', now()->subMinutes($initialDelay))
            ->get();

        $this->info("Found {$newRequests->count()} new requests without reminder record");

        foreach ($newRequests as $request) {
            // Check apakah sudah ada dokumen yang dikirim
            $documentCount = $request->requestDocuments()
                ->whereNotNull('file_path')
                ->whereIn('status', ['SUBMITTED', 'APPROVED', 'REVISE'])
                ->count();

            if ($documentCount > 0) {
                // Sudah ada dokumen, buat reminder tapi langsung set STOPPED
                $reminder = DocumentRequestReminder::create([
                    'document_request_id' => $request->id,
                    'reminder_level' => 0,
                    'escalation_status' => 'STOPPED',
                    'stopped_at' => now(),
                    'document_count_at_last_check' => $documentCount,
                ]);

                $this->line("  ⏭️  Request #{$request->id} - Already has {$documentCount} documents, marked as STOPPED");
                continue;
            }

            // Belum ada dokumen, buat reminder dan set untuk kirim segera (dengan delay 5 detik untuk avoid race condition)
            $reminder = DocumentRequestReminder::create([
                'document_request_id' => $request->id,
                'reminder_level' => 1, // Level 1 = PIC
                'escalation_status' => 'WAITING',
                'next_reminder_at' => now()->addSeconds(5), // Kirim 5 detik lagi, avoid race condition dengan checkExistingReminders
                'document_count_at_last_check' => 0,
            ]);

            $this->line("  📧 Request #{$request->id} - Created reminder, scheduled to send to PIC in 5 seconds");
        }
    }

    /**
     * Check existing reminders yang sudah ready untuk dikirim
     */
    private function checkExistingReminders()
    {
        // Cari reminder yang:
        // 1. Status WAITING (belum stopped)
        // 2. next_reminder_at sudah lewat
        // 3. Belum sampai level maksimal ATAU belum escalated to director
        $readyReminders = DocumentRequestReminder::where('escalation_status', 'WAITING')
            ->whereNotNull('next_reminder_at')
            ->where('next_reminder_at', '<=', now())
            ->where(function ($query) {
                $query->where('reminder_level', '<', 5)
                    ->orWhere('escalation_status', '!=', 'ESCALATED_TO_DIRECTOR');
            })
            ->get();

        $this->info("Found {$readyReminders->count()} reminders ready to send");

        foreach ($readyReminders as $reminder) {
            $request = $reminder->documentRequest;

            // Double check: apakah sudah ada dokumen yang dikirim?
            $documentCount = $request->requestDocuments()
                ->whereNotNull('file_path')
                ->whereIn('status', ['SUBMITTED', 'APPROVED', 'REVISE'])
                ->count();

            if ($documentCount > 0) {
                // Ada dokumen, stop reminder
                $reminder->stop();
                $this->line("  🛑 Request #{$request->id} - Documents submitted ({$documentCount}), reminder stopped");
                continue;
            }

            // Tentukan level yang akan dikirim
            // reminder_level menunjukkan level berikutnya yang akan dikirim
            $levelToSend = $reminder->reminder_level;

            if ($levelToSend > 5) {
                // Sudah melewati level maksimal
                $this->line("  ⏭️  Request #{$request->id} - Already at max escalation level");
                continue;
            }

            $this->line("  📧 Request #{$request->id} - Sending escalation level {$levelToSend}");

            // Dispatch job
            NoSubmissionReminderJob::dispatch($request, $reminder, $levelToSend);
        }
    }

    /**
     * Check reminder yang perlu di-stop karena ada dokumen yang dikirim
     */
    private function checkStopConditions()
    {
        // Cari reminder yang status WAITING (belum stopped)
        $activeReminders = DocumentRequestReminder::where('escalation_status', 'WAITING')->get();

        $this->info("Checking {$activeReminders->count()} active reminders for stop conditions");

        $stoppedCount = 0;

        foreach ($activeReminders as $reminder) {
            $request = $reminder->documentRequest;

            $documentCount = $request->requestDocuments()
                ->whereNotNull('file_path')
                ->whereIn('status', ['SUBMITTED', 'APPROVED', 'REVISE'])
                ->count();

            if ($documentCount > 0) {
                $reminder->stop();
                $stoppedCount++;
                $this->line("  🛑 Request #{$request->id} - Documents submitted ({$documentCount}), reminder stopped");
            }
        }

        if ($stoppedCount === 0) {
            $this->line("  ✓ No reminders need to be stopped");
        } else {
            $this->info("Stopped {$stoppedCount} reminders due to document submission");
        }
    }
}
