<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InspectionStandard;
use App\Services\InspectionStandardPdfGenerator;
use Illuminate\Support\Facades\Storage;

class TestPdfGeneration extends Command
{
    protected $signature = 'test:pdf-generation';
    protected $description = 'Test PDF generation for Inspection Standard';

    public function handle()
    {
        $this->info('Testing PDF generation...');

        $is = InspectionStandard::with(['drawings', 'inspectionItems', 'supplier'])->first();

        if (!$is) {
            $this->error('No inspection standard found');
            return 1;
        }

        $this->info("IS ID: {$is->id}");
        $this->info("Part No: {$is->part_no}");
        $this->info("Part Name: {$is->part_name}");

        try {
            $generator = new InspectionStandardPdfGenerator();
            $pdfPath = $generator->generate($is);
            $this->info('✓ PDF generated successfully');
            $this->info("Path: {$pdfPath}");

            // Check if file exists
            $fullPath = Storage::disk('documents')->path($pdfPath);
            if (file_exists($fullPath)) {
                $fileSize = filesize($fullPath);
                $this->info('✓ File exists');
                $this->info('Size: ' . number_format($fileSize) . ' bytes');
                $this->info("Full path: {$fullPath}");
            } else {
                $this->error("File does not exist at {$fullPath}");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}
