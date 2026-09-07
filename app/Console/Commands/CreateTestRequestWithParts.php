<?php

namespace App\Console\Commands;

use App\Models\DocumentRequest;
use App\Models\RequestPart;
use App\Models\Supplier;
use App\Models\User;
use App\Services\RequestService;
use Illuminate\Console\Command;

class CreateTestRequestWithParts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:create-request-with-parts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a test document request with multiple parts for testing';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(RequestService $requestService)
    {
        $this->info('Creating test document request with multiple parts...');

        // Get first supplier
        $supplier = Supplier::first();
        if (!$supplier) {
            $this->error('No supplier found. Please run supplier seeder first.');
            return 1;
        }

        // Get QA admin user
        $qaAdmin = User::where('role', 'admin_qa')->first();
        if (!$qaAdmin) {
            $this->error('No QA Admin user found. Please create admin users first.');
            return 1;
        }

        // Create request data
        $data = [
            'supplier_id' => $supplier->id,
            'model' => 'Toyota Avanza 2024',
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'parts' => [
                [
                    'part_name' => 'Brake Pad Front',
                    'part_number' => 'AK-148510-RR',
                ],
                [
                    'part_name' => 'Brake Pad Rear',
                    'part_number' => 'AK-148511-RR',
                ],
                [
                    'part_name' => 'Brake Disc Front',
                    'part_number' => 'AK-148512-RR',
                ],
            ],
        ];

        try {
            $documentRequest = $requestService->createRequestWithParts($data, $qaAdmin->id);

            // Send request letter email
            \App\Jobs\SendRequestLetterJob::dispatch($documentRequest);

            $this->info('✓ Document request created successfully!');
            $this->info('  Request ID: ' . $documentRequest->id);
            $this->info('  Model: ' . $documentRequest->model);
            $this->info('  Supplier: ' . $documentRequest->supplier->name);
            $this->info('  Due Date: ' . $documentRequest->due_date->format('d M Y'));
            
            $this->newLine();
            $this->info('Parts created:');
            foreach ($documentRequest->requestParts as $part) {
                $this->info('  - ' . $part->part_name . ' (' . $part->part_number . ')');
                $this->info('    Documents: ' . $part->requestDocuments()->count());
            }

            $this->newLine();
            $this->info('Total documents created: ' . $documentRequest->requestDocuments()->count());
            $this->info('Access token: ' . $documentRequest->access_token);
            $this->info('📧 Email job dispatched to queue');

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to create request: ' . $e->getMessage());
            return 1;
        }
    }
}
