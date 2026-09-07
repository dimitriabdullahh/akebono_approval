<?php

namespace App\Console\Commands;

use App\Models\DocumentRequest;
use Illuminate\Console\Command;

class DebugMultiPartRender extends Command
{
    protected $signature = 'debug:multipart-render {token}';
    protected $description = 'Debug multi-part rendering issue';

    public function handle()
    {
        $token = $this->argument('token');
        
        $request = DocumentRequest::where('access_token', $token)
            ->with([
                'supplier', 
                'requestParts.requestDocuments.documentType',
                'requestParts.requestDocuments.histories',
                'requestDocuments.documentType', 
                'requestDocuments.histories'
            ])
            ->first();

        if (!$request) {
            $this->error('Request not found');
            return 1;
        }

        $this->info('Request ID: ' . $request->id);
        $this->info('Model: ' . $request->model);
        $this->info('Has Multiple Parts: ' . ($request->hasMultipleParts() ? 'YES' : 'NO'));
        $this->info('Parts Count: ' . $request->requestParts->count());
        $this->newLine();

        foreach ($request->requestParts as $index => $part) {
            $this->info("=== Part {$index}: {$part->part_name} ===");
            $this->info("  ID: {$part->id}");
            $this->info("  Part Number: {$part->part_number}");
            $this->info("  Documents: " . $part->requestDocuments->count());
            
            $mandatory = $part->requestDocuments()->whereHas('documentType', function($q) {
                $q->where('is_mandatory', true);
            })->count();
            
            $uploaded = $part->requestDocuments()->whereHas('documentType', function($q) {
                $q->where('is_mandatory', true);
            })->whereNotNull('file_path')->whereIn('status', ['SUBMITTED', 'APPROVED', 'REVISE'])->count();
            
            $this->info("  Mandatory: {$mandatory}, Uploaded: {$uploaded}");
            
            // Show first 3 documents
            $this->info("  First 3 documents:");
            foreach ($part->requestDocuments->take(3) as $doc) {
                $this->info("    - {$doc->documentType->name} (Status: {$doc->status})");
            }
            $this->newLine();
        }

        return 0;
    }
}
