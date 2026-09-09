<?php

namespace App\Console\Commands;

use App\Models\DocumentRequest;
use App\Models\DocumentRequestReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetBundleReminderForTesting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:reset-for-testing
                            {request_id? : Specific request ID to reset (optional)}
                            {--all : Reset ALL active reminders}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset bundle submission reminders to test new configuration. Warning: This will reset escalation progress!';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $requestId = $this->argument('request_id');
        $resetAll = $this->option('all');

        if (!$requestId && !$resetAll) {
            $this->error('❌ Please specify either:');
            $this->line('   - reminder:reset-for-testing <request_id>  [Reset specific request]');
            $this->line('   - reminder:reset-for-testing --all           [Reset ALL reminders]');
            return 1;
        }

        if ($resetAll) {
            return $this->resetAllReminders();
        }

        return $this->resetSingleReminder($requestId);
    }

    /**
     * Reset a specific request's reminder
     */
    private function resetSingleReminder($requestId)
    {
        $request = DocumentRequest::find($requestId);

        if (!$request) {
            $this->error("❌ Request #{$requestId} not found");
            return 1;
        }

        $reminder = $request->reminder;

        if (!$reminder) {
            $this->warn("⚠️  Request #{$requestId} has no reminder record yet.");
            $this->info("Creating new reminder record...");
            
            $reminder = DocumentRequestReminder::create([
                'document_request_id' => $request->id,
                'reminder_level' => 1,
                'escalation_status' => 'WAITING',
                'next_reminder_at' => now(),
            ]);

            $this->info("✅ New reminder created for request #{$requestId}");
            $this->displayReminderInfo($request, $reminder);
            return 0;
        }

        $oldLevel = $reminder->reminder_level;
        $oldStatus = $reminder->escalation_status;

        // Reset to level 1, ready to send immediately
        $reminder->update([
            'reminder_level' => 1,
            'escalation_status' => 'WAITING',
            'pic_reminder_sent_at' => null,
            'supervisor_escalation_sent_at' => null,
            'head_dept_escalation_sent_at' => null,
            'gm_escalation_sent_at' => null,
            'director_escalation_sent_at' => null,
            'next_reminder_at' => now(),
            'last_reminder_at' => null,
            'stopped_at' => null,
        ]);

        $this->info("✅ Reminder reset successfully!");
        $this->line("   Model: {$request->model}");
        $this->line("   Supplier: {$request->supplier->name}");
        $this->line("   Previous Level: {$oldLevel} ({$oldStatus})");
        $this->line("   New Level: 1 (WAITING)");
        $this->line("   Next Reminder: Now (Immediately)");
        $this->line("");
        $this->info("🔄 Reminder will be sent on next scheduler run (every 1 minute)");

        return 0;
    }

    /**
     * Reset all active reminders
     */
    private function resetAllReminders()
    {
        if (!$this->confirm('⚠️  Reset ALL active reminders? This will reset escalation progress to level 1!', false)) {
            $this->line('Cancelled.');
            return 0;
        }

        $reminders = DocumentRequestReminder::where('escalation_status', '!=', 'STOPPED')
            ->with('documentRequest.supplier')
            ->get();

        if ($reminders->isEmpty()) {
            $this->warn('No active reminders found to reset.');
            return 0;
        }

        $this->info("Resetting {$reminders->count()} reminder(s)...\n");

        $resetCount = 0;

        foreach ($reminders as $reminder) {
            $request = $reminder->documentRequest;

            // Skip if all parts already submitted
            $allSubmitted = $request->requestParts()
                ->where('submission_status', 'DRAFT')
                ->count() === 0;

            if ($allSubmitted) {
                $this->line("⏭️  Request #{$request->id} ({$request->model}): All parts submitted, skipping");
                continue;
            }

            $reminder->update([
                'reminder_level' => 1,
                'escalation_status' => 'WAITING',
                'pic_reminder_sent_at' => null,
                'supervisor_escalation_sent_at' => null,
                'head_dept_escalation_sent_at' => null,
                'gm_escalation_sent_at' => null,
                'director_escalation_sent_at' => null,
                'next_reminder_at' => now(),
                'last_reminder_at' => null,
                'stopped_at' => null,
            ]);

            $this->line("✅ Request #{$request->id} ({$request->model}): Reset to Level 1");
            $resetCount++;
        }

        $this->newLine();
        $this->info("✅ Reset complete! {$resetCount} reminder(s) reset to Level 1");
        $this->info("🔄 Reminders will be sent on next scheduler run (every 1 minute)");

        return 0;
    }

    /**
     * Display reminder info
     */
    private function displayReminderInfo($request, $reminder)
    {
        $this->line("   Model: {$request->model}");
        $this->line("   Supplier: {$request->supplier->name}");
        $this->line("   Level: {$reminder->reminder_level}");
        $this->line("   Status: {$reminder->escalation_status}");
        $this->line("   Next Reminder: " . ($reminder->next_reminder_at ? $reminder->next_reminder_at->format('Y-m-d H:i:s') : 'Not set'));
    }
}
