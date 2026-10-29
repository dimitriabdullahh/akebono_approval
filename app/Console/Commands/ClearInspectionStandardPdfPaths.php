<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InspectionStandard;

class ClearInspectionStandardPdfPaths extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inspection-standard:clear-pdfs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear PDF file paths from inspection_standards table to force re-generation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $updated = InspectionStandard::query()->update(['pdf_file_path' => null]);
            
            $this->info("✓ Successfully cleared pdf_file_path for {$updated} records");
            $this->line('Next time PDFs are generated, they will use the updated template.');
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
    }
}
