<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\DocumentRequest;
use Illuminate\Console\Command;

class TestAutoLoginUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:auto-login-url {userId?} {requestId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test auto-login URL generation for supplier';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userId = $this->argument('userId');
        $requestId = $this->argument('requestId');

        // If no arguments provided, find first supplier user and document request
        if (!$userId || !$requestId) {
            $this->info('No userId or requestId provided. Finding first available supplier and request...');
            
            $user = User::where('role', 'supplier')
                ->where('is_active', true)
                ->first();

            if (!$user) {
                $this->error('No active supplier user found in database.');
                return 1;
            }

            $documentRequest = DocumentRequest::where('supplier_id', $user->supplier_id)->first();

            if (!$documentRequest) {
                $this->error('No document request found for supplier: ' . $user->supplier->name);
                return 1;
            }

            $userId = $user->id;
            $requestId = $documentRequest->id;

            $this->info("Using User ID: {$userId} ({$user->name})");
            $this->info("Using Request ID: {$requestId} (Model: {$documentRequest->model})");
            $this->newLine();
        }

        // Verify user exists and is supplier
        $user = User::find($userId);
        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return 1;
        }

        if ($user->role !== 'supplier') {
            $this->error("User {$user->name} is not a supplier (role: {$user->role}).");
            return 1;
        }

        if (!$user->is_active) {
            $this->warn("Warning: User {$user->name} is not active.");
        }

        // Verify document request exists
        $documentRequest = DocumentRequest::find($requestId);
        if (!$documentRequest) {
            $this->error("Document request with ID {$requestId} not found.");
            return 1;
        }

        // Verify user belongs to the supplier
        if ($user->supplier_id !== $documentRequest->supplier_id) {
            $this->error("User does not belong to the supplier of this document request.");
            $this->error("User Supplier ID: {$user->supplier_id}");
            $this->error("Request Supplier ID: {$documentRequest->supplier_id}");
            return 1;
        }

        $this->info('✓ Validation passed');
        $this->newLine();

        // Generate signed URL
        $this->info('Generating signed auto-login URL...');
        $signedUrl = $user->generateSignedLoginUrl($requestId);

        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('AUTO-LOGIN URL GENERATED SUCCESSFULLY');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();

        $this->table(
            ['Field', 'Value'],
            [
                ['User ID', $user->id],
                ['User Name', $user->name],
                ['User Email', $user->email],
                ['Supplier ID', $user->supplier_id],
                ['Supplier Name', $user->supplier->name],
                ['Request ID', $documentRequest->id],
                ['Request Model', $documentRequest->model],
                ['Request Due Date', $documentRequest->due_date->format('d M Y')],
                ['URL Expires In', '72 hours (3 days)'],
            ]
        );

        $this->newLine();
        $this->info('Generated URL:');
        $this->line($signedUrl);
        $this->newLine();

        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('HOW TO TEST:');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->line('1. Copy the URL above');
        $this->line('2. Open it in a browser (incognito mode recommended)');
        $this->line('3. You should be automatically logged in as: ' . $user->name);
        $this->line('4. You should be redirected to the document request page');
        $this->newLine();

        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('SECURITY CHECKS:');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->line('✓ URL is signed and will expire in 72 hours');
        $this->line('✓ URL tampering will be detected');
        $this->line('✓ Only works for supplier role users');
        $this->line('✓ Verifies supplier ownership of request');
        $this->newLine();

        return 0;
    }
}
