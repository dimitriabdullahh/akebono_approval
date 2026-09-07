<?php

namespace App\Console\Commands;

use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\RequestDocument;
use Illuminate\Console\Command;

class FixMissingRequestDocuments extends Command
{
    protected $signature = 'fix:missing-request-documents {--request-id= : Specific request ID to fix}';
    protected $description = 'Fix missing request documents for document requests';

    public function handle()
    {
        $this->info('Fixing Missing Request Documents...');
        $this->newLine();

        $documentTypes = DocumentType::all();
        $this->info('Total Document Types in DB: ' . $documentTypes->count());
        $this->newLine();

        // Get document requests to fix
        $query = DocumentRequest::query();
        if ($requestId = $this->option('request-id')) {
            $query->where('id', $requestId);
        }
        
        $documentRequests = $query->with(['requestDocuments', 'supplier'])->get();

        if ($documentRequests->isEmpty()) {
            $this->error('No document requests found!');
            return 1;
        }

        $fixedCount = 0;
        $createdCount = 0;

        foreach ($documentRequests as $request) {
            $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info('Processing Request ID: ' . $request->id);
            $this->info('Supplier: ' . $request->supplier->name);
            $this->info('Model: ' . $request->model);
            $this->info('Current Request Documents: ' . $request->requestDocuments->count());

            // Get existing document type IDs
            $existingDocTypeIds = $request->requestDocuments->pluck('document_type_id')->toArray();

            // Find missing document types
            $missingDocTypes = $documentTypes->whereNotIn('id', $existingDocTypeIds);

            if ($missingDocTypes->isEmpty()) {
                $this->comment('✓ No missing documents');
            } else {
                $this->warn('⚠️  Found ' . $missingDocTypes->count() . ' missing documents');
                
                if ($this->confirm('Create missing request documents for this request?', true)) {
                    foreach ($missingDocTypes as $docType) {
                        RequestDocument::create([
                            'document_request_id' => $request->id,
                            'document_type_id' => $docType->id,
                            'status' => 'PENDING',
                        ]);
                        $this->line('  ✓ Created: ' . $docType->name);
                        $createdCount++;
                    }
                    $fixedCount++;
                    $this->info('✅ Fixed! Now has ' . ($request->requestDocuments->count() + $missingDocTypes->count()) . ' documents');
                } else {
                    $this->comment('Skipped');
                }
            }

            $this->newLine();
        }

        $this->newLine();
        $this->info('Summary:');
        $this->info('  - Requests processed: ' . $documentRequests->count());
        $this->info('  - Requests fixed: ' . $fixedCount);
        $this->info('  - Documents created: ' . $createdCount);

        return 0;
    }
}
