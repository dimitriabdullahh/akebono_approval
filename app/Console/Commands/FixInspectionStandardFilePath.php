<?php

namespace App\Console\Commands;

use App\Models\RequestDocument;
use App\Models\InspectionStandard;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixInspectionStandardFilePath extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:inspection-standard-file-path';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix missing file_path in request_documents for Inspection Standards that have generated PDFs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Looking for Inspection Standards with missing file_path...');

        // Find all request documents for IS type where file_path is null/empty
        // but the inspection standard has a pdf_file_path
        $requestDocuments = RequestDocument::whereHas('documentType', function ($q) {
            $q->where('code', 'IS');
        })
        ->whereHas('inspectionStandard', function ($q) {
            $q->whereNotNull('pdf_file_path');
        })
        ->where(function($q) {
            $q->whereNull('file_path')
              ->orWhere('file_path', '');
        })
        ->with('inspectionStandard')
        ->get();

        if ($requestDocuments->isEmpty()) {
            $this->info('✅ No documents need fixing. All Inspection Standards already have file_path set.');
            return 0;
        }

        $this->info("📝 Found {$requestDocuments->count()} document(s) to fix:");
        $this->newLine();

        $updated = 0;
        foreach ($requestDocuments as $doc) {
            $is = $doc->inspectionStandard;
            
            $this->line("  • Request Document ID: {$doc->id}");
            $this->line("    Status: {$doc->status}");
            $this->line("    IS PDF Path: {$is->pdf_file_path}");
            
            DB::beginTransaction();
            try {
                $doc->update([
                    'file_path' => $is->pdf_file_path
                ]);
                
                DB::commit();
                $this->info("    ✅ Updated!");
                $updated++;
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("    ❌ Failed: {$e->getMessage()}");
            }
            
            $this->newLine();
        }

        $this->info("🎉 Done! Updated {$updated} out of {$requestDocuments->count()} document(s).");
        
        return 0;
    }
}
