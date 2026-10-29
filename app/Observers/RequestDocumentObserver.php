<?php

namespace App\Observers;

use App\Models\RequestDocument;
use App\Models\PartSubmissionWarrant;
use App\Models\PackagingStandard;
use App\Models\InspectionStandard;
use App\Models\MsaGageRr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RequestDocumentObserver
{
    /**
     * Handle the RequestDocument "created" event.
     * Automatically create IS/PSW/PS/MSA form instances when RequestDocument is created
     * dengan status PENDING (belum diisi)
     */
    public function created(RequestDocument $requestDocument)
    {
        // Load the document type and request
        if (!$requestDocument->relationLoaded('documentType')) {
            $requestDocument->load('documentType');
        }
        if (!$requestDocument->relationLoaded('documentRequest')) {
            $requestDocument->load(['documentRequest.supplier', 'requestPart']);
        }

        $documentTypeCode = $requestDocument->documentType->code ?? null;
        $supplierId = $requestDocument->documentRequest->supplier_id ?? null;

        try {
            // Create IS form if document type is IS
            // Status awal: PENDING (belum diisi apapun)
            if ($documentTypeCode === 'IS' && $supplierId) {
                // Check if IS already exists
                if (!InspectionStandard::where('request_document_id', $requestDocument->id)->exists()) {
                    InspectionStandard::create([
                        'request_document_id' => $requestDocument->id,
                        'supplier_id' => $supplierId,
                        'status' => 'PENDING',
                    ]);
                    
                    Log::info("Auto-created IS form with PENDING status", [
                        'request_document_id' => $requestDocument->id,
                        'supplier_id' => $supplierId,
                    ]);
                }
            }
            
            // Create PSW form if document type is PSW
            // Status awal: PENDING (belum diisi apapun)
            if ($documentTypeCode === 'PSW' && $supplierId) {
                // Check if PSW already exists
                if (!PartSubmissionWarrant::where('request_document_id', $requestDocument->id)->exists()) {
                    PartSubmissionWarrant::create([
                        'request_document_id' => $requestDocument->id,
                        'supplier_id' => $supplierId,
                        'status' => 'PENDING',
                    ]);
                    
                    Log::info("Auto-created PSW form with PENDING status", [
                        'request_document_id' => $requestDocument->id,
                        'supplier_id' => $supplierId,
                    ]);
                }
            }

            // Create PS form if document type is PACKAGING_STANDARD
            // Status awal: PENDING (belum diisi apapun)
            if ($documentTypeCode === 'PACKAGING_STANDARD' && $supplierId) {
                // Check if PS already exists
                if (!PackagingStandard::where('request_document_id', $requestDocument->id)->exists()) {
                    PackagingStandard::create([
                        'request_document_id' => $requestDocument->id,
                        'supplier_id' => $supplierId,
                        'status' => 'PENDING',
                        'customer' => 'AAIJ (Purchasing & Quality Division)',
                    ]);
                    
                    Log::info("Auto-created PS form with PENDING status", [
                        'request_document_id' => $requestDocument->id,
                        'supplier_id' => $supplierId,
                    ]);
                }
            }
            
            // Create MSA Gage R&R form if document type is MSA
            // Status awal: draft (belum diisi apapun)
            if ($documentTypeCode === 'MSA' && $supplierId) {
                // Check if MSA already exists
                if (!MsaGageRr::where('request_document_id', $requestDocument->id)->exists()) {
                    // Generate unique token
                    $token = Str::random(32);
                    while (MsaGageRr::where('token', $token)->exists()) {
                        $token = Str::random(32);
                    }
                    
                    // Get part info from RequestPart if exists
                    $partNo = $requestDocument->requestPart->part_number ?? null;
                    $partName = $requestDocument->requestPart->part_name ?? null;
                    
                    MsaGageRr::create([
                        'request_document_id' => $requestDocument->id,
                        'supplier_id' => $supplierId,
                        'token' => $token,
                        'part_no' => $partNo,
                        'part_name' => $partName,
                        'status' => 'PENDING', // Initial status should be PENDING, not draft
                    ]);
                    
                    Log::info("Auto-created MSA Gage R&R form with PENDING status", [
                        'request_document_id' => $requestDocument->id,
                        'supplier_id' => $supplierId,
                        'token' => $token,
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
