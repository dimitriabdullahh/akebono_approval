<?php

namespace App\Console\Commands;

use App\Models\DocumentRequest;
use App\Models\DocumentRequestReminder;
use App\Models\RequestPart;
use Illuminate\Console\Command;

class TestBundleSubmissionReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:test-bundle 
                            {--list : List semua bundle reminders}
                            {--reset : Reset semua bundle reminders}
                            {--status= : Check status reminder untuk request ID tertentu}
                            {--submit= : Simulate submit bundle untuk part ID tertentu}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testing utility untuk Bundle Submission Reminder System';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->option('list')) {
            return $this->listReminders();
        }

        if ($this->option('reset')) {
            return $this->resetReminders();
        }

        if ($this->option('status')) {
            return $this->checkStatus($this->option('status'));
        }

        if ($this->option('submit')) {
            return $this->submitBundle($this->option('submit'));
        }

        $this->info('🧪 Bundle Submission Reminder Test Utility');
        $this->info('');
        $this->info('Available options:');
        $this->info('  --list                List semua bundle reminder yang aktif');
        $this->info('  --reset               Reset semua bundle reminder');
        $this->info('  --status=<id>         Check status reminder untuk request ID tertentu');
        $this->info('  --submit=<id>         Simulate submit bundle untuk part ID tertentu');
        $this->info('');
        $this->info('Note: Test requests are already created. Check existing requests.');

        return 0;
    }

    private function listReminders()
    {
        $reminders = DocumentRequestReminder::with('documentRequest.supplier', 'documentRequest.requestParts')->get();

        if ($reminders->isEmpty()) {
            $this->info('📭 No bundle reminders found.');
            return 0;
        }

        $this->info("📋 Active Bundle Reminders ({$reminders->count()}):");
        $this->info('');

        $headers = ['ID', 'Request ID', 'Model', 'Supplier', 'Parts', 'Level', 'Status', 'Next Reminder'];
        $rows = [];

        foreach ($reminders as $reminder) {
            $partsCount = $reminder->documentRequest->requestParts->count();
            $draftCount = $reminder->documentRequest->requestParts->where('submission_status', 'DRAFT')->count();
            
            $rows[] = [
                $reminder->id,
                $reminder->document_request_id,
                \Illuminate\Support\Str::limit($reminder->documentRequest->model, 15),
                \Illuminate\Support\Str::limit($reminder->documentRequest->supplier->name, 20),
                "{$draftCount}/{$partsCount} pending",
                $reminder->reminder_level . ' - ' . $reminder->getLevelName(),
                $reminder->escalation_status,
                $reminder->next_reminder_at ? $reminder->next_reminder_at->format('H:i:s') : '-',
            ];
        }

        $this->table($headers, $rows);

        return 0;
    }

    private function resetReminders()
    {
        $count = DocumentRequestReminder::count();

        if ($count === 0) {
            $this->info('📭 No reminders to reset.');
            return 0;
        }

        if (!$this->confirm("⚠️  This will delete {$count} reminder(s). Continue?")) {
            $this->info('Cancelled.');
            return 0;
        }

        DocumentRequestReminder::truncate();

        $this->info("✅ {$count} reminder(s) deleted successfully.");

        return 0;
    }

    private function checkStatus($requestId)
    {
        $request = DocumentRequest::with('reminder', 'supplier', 'requestParts.requestDocuments.documentType')->find($requestId);

        if (!$request) {
            $this->error("❌ Request ID {$requestId} not found.");
            return 1;
        }

        $this->info("📋 Document Request #{$request->id} Status:");
        $this->info('');
        $this->info("Model: {$request->model}");
        $this->info("Supplier: {$request->supplier->name}");
        $this->info("Created At: {$request->created_at->format('Y-m-d H:i:s')} ({$request->created_at->diffForHumans()})");
        $this->info("Total Parts: {$request->requestParts->count()}");
        $this->info('');

        // Parts progress
        $this->info("📦 Parts Status:");
        foreach ($request->requestParts as $part) {
            $totalDocs = $part->requestDocuments()->count();
            $uploadedDocs = $part->requestDocuments()
                ->whereNotNull('file_path')
                ->whereIn('status', ['SUBMITTED', 'APPROVED', 'REVISE'])
                ->count();

            $statusIcon = $part->submission_status === 'SUBMITTED' ? '✓' : '○';
            $this->info("   {$statusIcon} {$part->part_name} ({$part->part_number})");
            $this->info("      Status: {$part->submission_status}");
            $this->info("      Documents: {$uploadedDocs} / {$totalDocs}");
        }
        $this->info('');

        // Overall progress
        $submittedParts = $request->requestParts->where('submission_status', 'SUBMITTED')->count();
        $draftParts = $request->requestParts->where('submission_status', 'DRAFT')->count();
        
        $this->info("📊 Overall Progress:");
        $this->info("   Submitted Parts: {$submittedParts} / {$request->requestParts->count()}");
        $this->info("   Pending Parts: {$draftParts}");
        $this->info('');

        // Reminder status
        if (!$request->reminder) {
            $initialDelay = config('reminder.initial_reminder_delay', 3);
            $willSendAt = $request->created_at->copy()->addMinutes($initialDelay);
            
            $this->warn('⏳ No reminder record yet.');
            
            if (now()->lt($willSendAt)) {
                $this->info("   Reminder will be created at: {$willSendAt->format('Y-m-d H:i:s')}");
                $this->info("   Time remaining: {$willSendAt->diffForHumans()}");
            } else {
                $this->info("   Reminder should be created now. Run: php artisan reminder:check-bundle-submission");
            }
        } else {
            $reminder = $request->reminder;
            
            $this->info('🔔 Reminder Status:');
            $this->info("   Level: {$reminder->reminder_level} - {$reminder->getLevelName()}");
            $this->info("   Status: {$reminder->escalation_status}");
            
            if ($reminder->pic_reminder_sent_at) {
                $this->info("   ✓ PIC Reminder: {$reminder->pic_reminder_sent_at->format('Y-m-d H:i:s')}");
            }
            
            if ($reminder->supervisor_escalation_sent_at) {
                $this->info("   ✓ Supervisor: {$reminder->supervisor_escalation_sent_at->format('Y-m-d H:i:s')}");
            }
            
            if ($reminder->head_dept_escalation_sent_at) {
                $this->info("   ✓ Head Dept: {$reminder->head_dept_escalation_sent_at->format('Y-m-d H:i:s')}");
            }
            
            if ($reminder->gm_escalation_sent_at) {
                $this->info("   ✓ GM: {$reminder->gm_escalation_sent_at->format('Y-m-d H:i:s')}");
            }
            
            if ($reminder->director_escalation_sent_at) {
                $this->info("   ✓ Director: {$reminder->director_escalation_sent_at->format('Y-m-d H:i:s')}");
            }
            
            $this->info('');
            
            if ($reminder->next_reminder_at) {
                if (now()->gte($reminder->next_reminder_at)) {
                    $this->warn("   ⏰ Next reminder: READY TO SEND NOW");
                } else {
                    $this->info("   Next reminder: {$reminder->next_reminder_at->format('Y-m-d H:i:s')} ({$reminder->next_reminder_at->diffForHumans()})");
                }
            } else {
                $this->info("   Next reminder: None (stopped or max level)");
            }
            
            if ($reminder->stopped_at) {
                $this->info("   🛑 Stopped at: {$reminder->stopped_at->format('Y-m-d H:i:s')}");
            }
        }

        return 0;
    }

    private function submitBundle($partId)
    {
        $part = RequestPart::find($partId);

        if (!$part) {
            $this->error("❌ Part ID {$partId} not found.");
            return 1;
        }

        if ($part->submission_status === 'SUBMITTED') {
            $this->warn("⚠️  Part #{$partId} is already SUBMITTED.");
            return 0;
        }

        // Simulate submit
        $part->update([
            'submission_status' => 'SUBMITTED',
            'submitted_at' => now(),
        ]);

        $this->info("✅ Part #{$partId} ({$part->part_number}) marked as SUBMITTED");
        $this->info('');
        $this->info("Run checker to stop reminder:");
        $this->info("  php artisan reminder:check-bundle-submission");

        return 0;
    }
}
