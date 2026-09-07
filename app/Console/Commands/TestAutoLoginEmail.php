<?php

namespace App\Console\Commands;

use App\Models\DocumentRequest;
use App\Jobs\SendRequestLetterJob;
use Illuminate\Console\Command;

class TestAutoLoginEmail extends Command
{
    protected $signature = 'test:auto-login-email {requestId?}';
    protected $description = 'Test sending email with auto-login URL';

    public function handle()
    {
        $requestId = $this->argument('requestId') ?? 1;
        
        $request = DocumentRequest::with('supplier.users')->find($requestId);
        
        if (!$request) {
            $this->error("Request ID {$requestId} not found");
            return 1;
        }

        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('TESTING AUTO-LOGIN EMAIL');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();

        $this->table(['Field', 'Value'], [
            ['Request ID', $request->id],
            ['Model', $request->model],
            ['Supplier', $request->supplier->name],
            ['PIC Email', $request->supplier->pic_email],
        ]);

        $this->newLine();

        // Get supplier user
        $supplierUser = $request->supplier->users()
            ->where('is_active', true)
            ->where('role', 'supplier')
            ->first();

        if ($supplierUser) {
            $this->info("✓ Supplier user found: {$supplierUser->name} (ID: {$supplierUser->id})");
            $loginUrl = $supplierUser->generateSignedLoginUrl($request->id);
            $this->info("✓ Generated signed URL:");
            $this->line("  {$loginUrl}");
        } else {
            $this->warn("⚠ No active supplier user found. Will use portal token fallback.");
            $loginUrl = null;
        }

        $this->newLine();
        $this->info('Dispatching email job with loginUrl...');
        
        SendRequestLetterJob::dispatch($request, $loginUrl);
        
        $this->info('✓ Job dispatched to queue');
        $this->newLine();
        
        $this->info('Processing queue...');
        $this->call('queue:work', ['--once' => true]);
        
        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('✓ EMAIL SENT!');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('Open Mailpit: http://localhost:8025');
        $this->info('Look for email to: ' . $request->supplier->pic_email);
        $this->info('Check the button URL - it should start with /auto-login/');
        $this->newLine();

        return 0;
    }
}
