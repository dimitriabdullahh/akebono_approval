<?php

namespace App\Observers;

use App\Models\RequestDocument;
use App\Models\PartSubmissionWarrant;
use App\Models\PackagingStandard;
use Illuminate\Support\Facades\Log;

class RequestDocumentObserver
{
    /**
     * Handle the RequestDocument "created" event.
     * Automatically create PSW/PS form instances when RequestDocument is created
     */
    public function created(RequestDocument $requestDocument)
    {
        // Load the document type and request
        if (!$requestDocument->relationLoaded('documentType')) {
            $requestDocument->load('documentType');
        }
        if (!$requestDocument->relationLoaded('documentRequest')) {
            $requestDocument->load(['documentRequest.supplier']);
        }

        $documentTypeCode = $requestDocument->documentType->code ?? null;
        $supplierId = $requestDocument->documentRequest->supplier_id ?? null;

        try {
            // Create PSW form if document type is PSW
            if ($documentTypeCode === 'PSW' && $supplierId) {
                // Check if PSW already exists
                if (!PartSubmissionWarrant::where('request_document_id', $requestDocument->id)->exists()) {
                    PartSubmissionWarrant::create([
                        'request_document_id' => $requestDocument->id,
                        'supplier_id' => $supplierId,
                        'status' => 'DRAFT',
                    ]);
                    
                    Log::info("Auto-created PSW form", [
                        'request_document_id' => $requestDocument->id,
                        'supplier_id' => $supplierId,
                    ]);
                }
            }

            // Create PS form if document type is PACKAGING_STANDARD
            if ($documentTypeCode === 'PACKAGING_STANDARD' && $supplierId) {
                // Check if PS already exists
                if (!PackagingStandard::where('request_document_id', $requestDocument->id)->exists()) {
                    PackagingStandard::create([
                        'request_document_id' => $requestDocument->id,
                        'supplier_id' => $supplierId,
                        'status' => 'DRAFT',
                        'customer' => 'AAIJ (Purchasing & Quality Division)',
                    ]);
                    
                    Log::info("Auto-created PS form", [
                        'request_document_id' => $requestDocument->id,
                        'supplier_id' => $supplierId,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("Error creating form instance in RequestDocumentObserver", [
                'request_document_id' => $requestDocument->id,
                'document_type_code' => $documentTypeCode,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the RequestDocument "updated" event.
     */
    public function updated(RequestDocument $requestDocument)
    {
        //
    }

    /**
     * Handle the RequestDocument "deleted" event.
     */
    public function deleted(RequestDocument $requestDocument)
    {
        //
    }
}
