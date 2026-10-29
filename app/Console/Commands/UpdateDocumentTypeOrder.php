<?php

namespace App\Console\Commands;

use App\Models\DocumentType;
use Illuminate\Console\Command;

class UpdateDocumentTypeOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:update-order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update document types order based on priority';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔄 Updating document types order...');
        $this->newLine();

        // Order mapping berdasarkan code
        $orderMapping = [
            // FORM-BASED DOCUMENTS (PRIORITY)
            'IS' => 1,
            'PACKAGING_STANDARD' => 2,
            'PSW' => 3,
            'MSA' => 4,  // MSA moved here (form-based)
            
            // UPLOAD-BASED DOCUMENTS (MANDATORY)
            'PFD' => 5,
            'FMEA' => 6,
            'QCPC' => 7,
            'ISIR' => 8,
            'AAR' => 9,
            'LIMIT_SAMPLE_TAG' => 10,
            'MILL_SHEET_MATERIAL' => 11,
            'CERTIFICATE_CALIBRATION' => 12,
            'DRAWING_PRODUCT' => 13,
            
            // OPTIONAL DOCUMENTS
            'SOC_FREE' => 14,
            'IMDS' => 15,
            
            // COMING SOON (FORM-BASED)
            'XR_CONTROL_CHART' => 16,
            'N1_DATA' => 17,
        ];

        $updated = 0;
        $errors = 0;

        foreach ($orderMapping as $code => $order) {
            $documentType = DocumentType::where('code', $code)->first();
            
            if ($documentType) {
                $documentType->order = $order;
                $documentType->save();
                
                $this->info("✓ Updated: {$documentType->name} -> Order: {$order}");
                $updated++;
            } else {
                $this->warn("✗ Not found: {$code}");
                $errors++;
            }
        }

        $this->newLine();
        $this->info("✅ Update complete!");
        $this->info("   Updated: {$updated} document types");
        
        if ($errors > 0) {
            $this->warn("   Errors: {$errors} document types not found");
        }

        return 0;
    }
}
