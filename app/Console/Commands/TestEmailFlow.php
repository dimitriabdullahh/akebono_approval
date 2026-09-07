<?php

namespace App\Console\Commands;

use App\Models\DocumentRequest;
use App\Models\Supplier;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class TestEmailFlow extends Command
{
    protected $signature = 'test:email-flow';
    protected $description = 'Test complete email flow: send notification and process queue';

    public function handle()
    {
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('🧪 TESTING EMAIL FLOW');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        // Get latest document request
        $request = DocumentRequest::with('supplier')->latest()->first();

        if (!$request) {
            $this->error('❌ No document request found!');
            $this->info('Create a document request first using the admin panel or commands.');
            return 1;
        }

        $this->info('Using Document Request:');
        $this->line("  ID: {$request->id}");
        $this->line("  Supplier: {$request->supplier->name}");
        $this->line("  Model: {$request->model}");
        $this->line("  PIC Email: {$request->supplier->pic_email}");
        $this->newLine();

        // Send email notification
        $this->info('📧 Dispatching email job...');
        $notificationService = app(NotificationService::class);
        $notificationService->sendRequestLetter($request);
        $this->line('✓ Email job dispatched to queue');
        $this->newLine();

        // Wait a moment
        $this->info('⏳ Waiting 2 seconds...');
        sleep(2);
        $this->newLine();

        // Process queue
        $this->info('⚙️  Processing queue...');
        $this->call('queue:work', [
            '--once' => true,
            '--tries' => 3,
        ]);
        $this->newLine();

        // Check results
        $this->info('📊 Checking results...');
        $this->call('check:email-queue');
        $this->newLine();

        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('💡 NEXT STEPS:');
        $this->line('  1. Open Mailpit: http://localhost:8025');
        $this->line('  2. Check if email is received');
        $this->line('  3. Verify email content and portal link');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        return 0;
    }
}
