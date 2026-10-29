<?php

namespace App\Console\Commands;

use App\Models\InspectionStandard;
use Illuminate\Console\Command;

class UpdateExistingInspectionStandardsWithAutoFill extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'is:update-autofill';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update existing Inspection Standards with auto-filled data from request';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating existing Inspection Standards...');

        $inspectionStandards = InspectionStandard::with([
            'requestDocument.documentRequest.supplier',
            'requestDocument.requestPart'
        ])->get();

        $updated = 0;

        foreach ($inspectionStandards as $is) {
            $needsUpdate = false;
            $updateData = [];

            // Get data from request
            $requestDocument = $is->requestDocument;
            if (!$requestDocument) {
                $this->warn("IS ID {$is->id}: No request document found");
                continue;
            }

            $documentRequest = $requestDocument->documentRequest;
            $requestPart = $requestDocument->requestPart;

            // Check and update MODEL
            if (empty($is->model) && !empty($documentRequest->model)) {
                $updateData['model'] = $documentRequest->model;
                $needsUpdate = true;
                $this->info("IS ID {$is->id}: Setting model to '{$documentRequest->model}'");
            }

            // Check and update PART NO
            if (empty($is->part_no) && $requestPart && !empty($requestPart->part_number)) {
                $updateData['part_no'] = $requestPart->part_number;
                $needsUpdate = true;
                $this->info("IS ID {$is->id}: Setting part_no to '{$requestPart->part_number}'");
            }

            // Check and update PART NAME
            if (empty($is->part_name) && $requestPart && !empty($requestPart->part_name)) {
                $updateData['part_name'] = $requestPart->part_name;
                $needsUpdate = true;
                $this->info("IS ID {$is->id}: Setting part_name to '{$requestPart->part_name}'");
            }

            // Check and update MANUFACTURER (should be supplier name)
            if (empty($is->manufacturer) && $documentRequest->supplier && !empty($documentRequest->supplier->name)) {
                $updateData['manufacturer'] = $documentRequest->supplier->name;
                $needsUpdate = true;
                $this->info("IS ID {$is->id}: Setting manufacturer to '{$documentRequest->supplier->name}'");
            }

            // Check and update PART ROUTE
            $supplierName = $documentRequest->supplier->name ?? '';
            $expectedPartRoute = "PT AAIJ → {$supplierName} → PT AAIJ";
            if (empty($is->part_route) || $is->part_route !== $expectedPartRoute) {
                $updateData['part_route'] = $expectedPartRoute;
                $needsUpdate = true;
                $this->info("IS ID {$is->id}: Setting part_route to '{$expectedPartRoute}'");
            }

            // Update if needed
            if ($needsUpdate) {
                $is->update($updateData);
                $updated++;
            }
        }

        $this->info("Updated {$updated} Inspection Standard(s) successfully!");

        return 0;
    }
}
