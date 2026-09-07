<?php

namespace App\Console\Commands;

use App\Models\DocumentRequest;
use App\Models\DocumentRequestReminder;
use App\Models\Supplier;
use Illuminate\Console\Command;

class TestNoSubmissionReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:test-no-submission 
                            {--create : Create test request untuk testing}
                            {--list : List semua reminders}
                            {--reset : Reset semua reminders}
                            {--status= : Check status reminder untuk request ID tertentu}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testing utility untuk No Submission Reminder System';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->option('create')) {
            return $this->createTestRequest();
        }

        if ($this->option('list')) {
            return $this->listReminders();
        }

        if ($this->option('reset')) {
            return $this->resetReminders();
        }

        if ($this->option('status')) {
            return $this->checkStatus($this->option('status'));
        }

        $this->info('🧪 No Submission Reminder Test Utility');
        $this->info('');
        $this->info('Available options:');
        $this->info('  --create              Create test request dengan supplier lengkap');
        $this->info('  --list                List semua reminder yang aktif');
        $this->info('  --reset               Reset semua reminder (hapus semua reminders)');
        $this->info('  --status=<id>         Check status reminder untuk request ID tertentu');
        $this->info('');
        $this->info('Examples:');
        $this->info('  php artisan reminder:test-no-submission --create');
        $this->info('  php artisan reminder:test-no-submission --list');
        $this->info('  php artisan reminder:test-no-submission --status=1');
        $this->info('  php artisan reminder:test-no-submission --reset');

        return 0;
    }

    private function createTestRequest()
    {
        $this->info('🔨 Creating test request for No Submission Reminder testing...');

        // Cari atau buat supplier test
        $supplier = Supplier::where('vendor_code', 'TEST-REMINDER')->first();
        
        if (!$supplier) {
            $supplier = Supplier::create([
                'name' => 'PT Test Reminder Supplier',
                'vendor_code' => 'TEST-REMINDER',
                'pic_name' => 'Test PIC',
                'pic_email' => 'pic@test-reminder.com',
                'pic_phone' => '081234567890',
                'supervisor_name' => 'Test Supervisor',
                'supervisor_email' => 'supervisor@test-reminder.com',
                'head_dept_name' => 'Test Head Department',
                'head_dept_email' => 'head@test-reminder.com',
                'gm_name' => 'Test General Manager',
                'gm_email' => 'gm@test-reminder.com',
                'director_name' => 'Test Director',
                'director_email' => 'director@test-reminder.com',
                'address' => 'Test Address',
            ]);
            $this->info("✓ Supplier created: {$supplier->name}");
        } else {
            $this->info("✓ Using existing supplier: {$supplier->name}");
        }

        // Cari admin QA untuk created_by
        $admin = \App\Models\User::where('role', 'admin_qa')->first();
        
        if (!$admin) {
            $this->error('❌ Admin QA not found. Please create an admin user first.');
            return 1;
        }

        // Buat document request tanpa dokumen
        $request = DocumentRequest::create([
            'supplier_id' => $supplier->id,
            'model' => 'Test Model ' . now()->format('YmdHis'),
            'due_date' => now()->addDays(7),
            'access_token' => \Illuminate\Support\Str::random(32),
            'created_by' => $admin->id,
        ]);

        $this->info("✓ Document Request created: ID {$request->id}");

        // Buat request part
        $part = \App\Models\RequestPart::create([
            'document_request_id' => $request->id,
            'part_name' => 'Test Brake Pad',
            'part_number' => 'TEST-' . now()->format('His'),
            'submission_status' => 'DRAFT',
        ]);

        $this->info("✓ Request Part created: {$part->part_number}");

        // Buat document placeholders (minimal 3 mandatory)
        $docTypes = \App\Models\DocumentType::where('is_mandatory', true)->take(3)->get();
        
        foreach ($docTypes as $docType) {
            \App\Models\RequestDocument::create([
                'document_request_id' => $request->id,
                'request_part_id' => $part->id,
                'document_type_id' => $docType->id,
                'status' => 'PENDING',
            ]);
        }

        $this->info("✓ Created {$docTypes->count()} document placeholders");

        $this->info('');
        $this->info('✅ Test request created successfully!');
        $this->info('');
        $this->info('📋 Request Details:');
        $this->info("   Request ID: {$request->id}");
        $this->info("   Supplier: {$supplier->name}");
        $this->info("   Model: {$request->model}");
        $this->info("   Part Number: {$part->part_number}");
        $this->info("   Created At: {$request->created_at->format('Y-m-d H:i:s')}");
        $this->info('');
        $this->info('⏱️  Timeline (dengan config testing):');
        $this->info("   +3 minutes: PIC Reminder");
        $this->info("   +5 minutes: Supervisor Escalation");
        $this->info("   +7 minutes: Head Department Escalation");
        $this->info("   +9 minutes: General Manager Escalation");
        $this->info("   +11 minutes: Director Escalation");
        $this->info('');
        $this->info('🚀 Next steps:');
        $this->info('   1. Wait 3 minutes');
        $this->info('   2. Check reminder status: php artisan reminder:test-no-submission --status=' . $request->id);
        $this->info('   3. Check emails in Mailpit: http://localhost:8025');
        $this->info('   4. Optional: Upload a document to stop reminder');

        return 0;
    }

    private function listReminders()
    {
        $reminders = DocumentRequestReminder::with('documentRequest.supplier')->get();

        if ($reminders->isEmpty()) {
            $this->info('📭 No reminders found.');
            return 0;
        }

        $this->info("📋 Active Reminders ({$reminders->count()}):");
        $this->info('');

        $headers = ['ID', 'Request ID', 'Supplier', 'Level', 'Status', 'Next Reminder', 'Created'];
        $rows = [];

        foreach ($reminders as $reminder) {
            $rows[] = [
                $reminder->id,
                $reminder->document_request_id,
                \Illuminate\Support\Str::limit($reminder->documentRequest->supplier->name, 20),
                $reminder->reminder_level . ' - ' . $reminder->getLevelName(),
                $reminder->escalation_status,
                $reminder->next_reminder_at ? $reminder->next_reminder_at->format('Y-m-d H:i:s') : '-',
                $reminder->created_at->format('Y-m-d H:i:s'),
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
        $request = DocumentRequest::with('reminder', 'supplier', 'requestParts')->find($requestId);

        if (!$request) {
            $this->error("❌ Request ID {$requestId} not found.");
            return 1;
        }

        $this->info("📋 Request #{$request->id} Status:");
        $this->info('');
        $this->info("Supplier: {$request->supplier->name}");
        $this->info("Model: {$request->model}");
        $this->info("Created At: {$request->created_at->format('Y-m-d H:i:s')} ({$request->created_at->diffForHumans()})");
        $this->info('');

        // Document count
        $docCount = $request->requestDocuments()
            ->whereNotNull('file_path')
            ->whereIn('status', ['SUBMITTED', 'APPROVED', 'REVISE'])
            ->count();

        $totalDocs = $request->requestDocuments()->count();

        $this->info("📄 Documents: {$docCount} / {$totalDocs} submitted");
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
                $this->info("   Reminder should be created now. Run: php artisan reminder:check-no-submission");
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
}
