<?php

namespace App\Console\Commands;

use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\RequestDocument;
use Illuminate\Console\Command;

class CheckRequestDocuments extends Command
{
    protected $signature = 'check:request-documents';
    protected $description = 'Check request documents for all document requests';

    public function handle()
    {
        $this->info('Checking Request Documents...');
        $this->newLine();

        $documentTypes = DocumentType::all();
        $this->info('Total Document Types in DB: ' . $documentTypes->count());
        $this->newLine();

        $documentRequests = DocumentRequest::with(['requestDocuments.documentType', 'supplier'])->get();
        
        foreach ($documentRequests as $req) {
            $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info('Document Request ID: ' . $req->id);
            $this->info('Supplier: ' . $req->supplier->name);
            $this->info('Model: ' . $req->model);
            $this->info('Total Request Documents: ' . $req->requestDocuments->count());
            $this->newLine();

            if ($req->requestDocuments->count() === 0) {
                $this->error('❌ NO REQUEST DOCUMENTS FOUND!');
            } else if ($req->requestDocuments->count() < $documentTypes->count()) {
                $this->warn('⚠️  INCOMPLETE! Expected ' . $documentTypes->count() . ' documents');
                $this->info('Missing ' . ($documentTypes->count() - $req->requestDocuments->count()) . ' documents');
            } else {
                $this->comment('✓ Complete (' . $req->requestDocuments->count() . ' documents)');
            }

            // Show first 5 documents
            if ($req->requestDocuments->count() > 0) {
                $this->newLine();
                $this->info('Sample documents:');
                foreach ($req->requestDocuments->take(5) as $doc) {
                    $this->line('  - ' . $doc->documentType->name . ' [' . $doc->status . ']');
                }
                if ($req->requestDocuments->count() > 5) {
                    $this->line('  ... and ' . ($req->requestDocuments->count() - 5) . ' more');
                }
            }
            
            $this->newLine();
        }

        return 0;
    }
}
