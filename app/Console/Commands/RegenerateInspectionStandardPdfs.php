<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InspectionStandard;
use App\Services\InspectionStandardPdfGenerator;

class RegenerateInspectionStandardPdfs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inspection-standard:regenerate {--id= : Specific inspection standard ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate PDFs for Inspection Standards with new template';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $generator = new InspectionStandardPdfGenerator();
        
        if ($this->option('id')) {
            // Regenerate specific IS
            $id = $this->option('id');
            $inspectionStandard = InspectionStandard::find($id);
            
            if (!$inspectionStandard) {
                $this->error("Inspection Standard with ID {$id} not found");
                return 1;
            }
            
            try {
                $path = $generator->generate($inspectionStandard);
                $this->info("✓ PDF regenerated for IS ID {$id}");
                $this->line("Path: {$path}");
                return 0;
            } catch (\Exception $e) {
                $this->error("Error generating PDF: " . $e->getMessage());
                return 1;
            }
        }
        
        // Regenerate all with DRAFT status
        $inspectionStandards = InspectionStandard::where('status', 'DRAFT')->get();
        
        if ($inspectionStandards->isEmpty()) {
            $this->info('No Inspection Standards with DRAFT status found');
            return 0;
        }
        
        $this->info("Found {$inspectionStandards->count()} Inspection Standards to regenerate");
        
        foreach ($inspectionStandards as $inspectionStandard) {
            try {
                $generator->generate($inspectionStandard);
                $this->info("✓ Regenerated: IS ID {$inspectionStandard->id} - {$inspectionStandard->part_name}");
            } catch (\Exception $e) {
                $this->error("✗ Failed for IS ID {$inspectionStandard->id}: " . $e->getMessage());
            }
        }
        
        return 0;
    }
}
