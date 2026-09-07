<?php

namespace App\Console\Commands;

use App\Jobs\BundleSubmissionReminderJob;
use App\Models\DocumentRequest;
use App\Models\DocumentRequestReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckBundleSubmissionReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:check-bundle-submission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check dan kirim reminder/escalation untuk bundle yang belum di-submit oleh supplier';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔍 Checking for document requests that need reminder...');

        // 1. Check untuk document request baru yang belum punya reminder record
        $this->checkNewDocumentRequests();

        // 2. Check untuk reminder yang sudah ready untuk dikirim
        $this->checkExistingReminders();

        // 3. Check untuk reminder yang perlu di-stop (semua parts sudah submitted)
        $this->checkStopConditions();

        $this->info('✅ Check completed!');

        return 0;
    }

    /**
     * Check document request baru yang belum punya reminder record
     */
    private function checkNewDocumentRequests()
    {
        $initialDelay = config('reminder.initial_reminder_delay', 3);

        // Cari document request yang:
        // 1. Dibuat lebih dari [initial_delay] menit yang lalu
        // 2. Belum punya reminder record
        // 3. Punya minimal 1 part dengan status DRAFT
        $newRequests = DocumentRequest::whereDoesntHave('reminder')
            ->where('created_at', '<=', now()->subMinutes($initialDelay))
            ->whereHas('requestParts', function($q) {
                $q->where('submission_status', 'DRAFT');
            })
            ->with('requestParts')
            ->get();

        $this->info("Found {$newRequests->count()} new document requests without reminder record");

        foreach ($newRequests as $request) {
            // Check apakah semua parts sudah submitted
            $allSubmitted = $request->requestParts()
                ->where('submission_status', 'DRAFT')
                ->count() === 0;

            if ($allSubmitted) {
                // Semua parts sudah submitted, buat reminder tapi langsung set STOPPED
                $reminder = DocumentRequestReminder::create([
                    'document_request_id' => $request->id,
                    'reminder_level' => 0,
                    'escalation_status' => 'STOPPED',
                    'stopped_at' => now(),
                ]);

                $this->line("  ⏭️  Request #{$request->id} (Model: {$request->model}) - All parts SUBMITTED, marked as STOPPED");
                continue;
            }

            // Ada parts yang belum submitted, buat reminder dan set untuk kirim sekarang
            $reminder = DocumentRequestReminder::create([
                'document_request_id' => $request->id,
                'reminder_level' => 1, // Level 1 = PIC
                'escalation_status' => 'WAITING',
                'next_reminder_at' => now(), // Kirim sekarang
            ]);

            $partsCount = $request->requestParts()->count();
            $draftCount = $request->requestParts()->where('submission_status', 'DRAFT')->count();
            
            $this->line("  📧 Request #{$request->id} (Model: {$request->model}, {$partsCount} parts, {$draftCount} pending) - Created reminder, ready to send to PIC");

            // Dispatch job untuk kirim email PIC
            BundleSubmissionReminderJob::dispatch($request, $reminder, 1);
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
            ->with('documentRequest.requestParts')
            ->get();

        $this->info("Found {$readyReminders->count()} reminders ready to send");

        foreach ($readyReminders as $reminder) {
            $request = $reminder->documentRequest;

            // Double check: apakah semua parts sudah submitted?
            $allSubmitted = $request->requestParts()
                ->where('submission_status', 'DRAFT')
                ->count() === 0;

            if ($allSubmitted) {
                // Semua parts sudah submitted, stop reminder
                $reminder->stop();
                $this->line("  🛑 Request #{$request->id} (Model: {$request->model}) - All parts SUBMITTED, reminder stopped");
                continue;
            }

            // Tentukan level yang akan dikirim
            $levelToSend = $reminder->reminder_level;

            if ($levelToSend > 5) {
                // Sudah melewati level maksimal
                $this->line("  ⏭️  Request #{$request->id} (Model: {$request->model}) - Already at max escalation level");
                continue;
            }

            $overallProgress = $reminder->getOverallProgressInfo();
            $this->line("  📧 Request #{$request->id} (Model: {$request->model}) - Sending escalation level {$levelToSend} (Parts: {$overallProgress['submitted_parts']}/{$overallProgress['total_parts']} submitted, Docs: {$overallProgress['uploaded_documents']}/{$overallProgress['total_documents']})");

            // Dispatch job
            BundleSubmissionReminderJob::dispatch($request, $reminder, $levelToSend);
        }
    }

    /**
     * Check reminder yang perlu di-stop karena semua parts sudah submitted
     */
    private function checkStopConditions()
    {
        // Cari reminder yang status WAITING (belum stopped)
        $activeReminders = DocumentRequestReminder::where('escalation_status', 'WAITING')
            ->with('documentRequest.requestParts')
            ->get();

        $this->info("Checking {$activeReminders->count()} active reminders for stop conditions");

        $stoppedCount = 0;

        foreach ($activeReminders as $reminder) {
            $request = $reminder->documentRequest;

            // Check apakah semua parts sudah submitted
            $allSubmitted = $request->requestParts()
                ->where('submission_status', 'DRAFT')
                ->count() === 0;

            if ($allSubmitted) {
                $reminder->stop();
                $stoppedCount++;
                $this->line("  🛑 Request #{$request->id} (Model: {$request->model}) - All parts SUBMITTED, reminder stopped");
            }
        }

        if ($stoppedCount === 0) {
            $this->line("  ✓ No reminders need to be stopped");
        } else {
            $this->info("Stopped {$stoppedCount} reminders due to bundle submission");
        }
    }
}
