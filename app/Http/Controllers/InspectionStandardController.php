<?php

namespace App\Http\Controllers;

use App\Models\InspectionStandard;
use App\Models\InspectionStandardDrawing;
use App\Models\InspectionStandardItem;
use App\Models\RequestDocument;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class InspectionStandardController extends Controller
{
    /**
     * Show Inspection Standard form
     * GET /inspection-standard/form/{token}
     */
    public function showForm($token)
    {
        try {
            // Verify token and get request document
            $requestDocument = RequestDocument::whereHas('documentRequest', function ($q) use ($token) {
                $q->where('access_token', $token);
            })
            ->whereHas('documentType', function ($q) {
                $q->where('code', 'IS');
            })
            ->firstOrFail();

            return view('inspection-standard.form', [
                'token' => $token,
                'requestDocument' => $requestDocument,
            ]);
        } catch (\Exception $e) {
            abort(404, 'Inspection Standard form not found');
        }
    }

    /**
     * Get or create Inspection Standard form for a document
     * GET /api/inspection-standards/{token}
     */
    public function getForm($token)
    {
        try {
            // Get request document via token
            $requestDocument = RequestDocument::whereHas('documentRequest', function ($q) use ($token) {
                $q->where('access_token', $token);
            })
            ->whereHas('documentType', function ($q) {
                $q->where('code', 'IS');
            })
            ->firstOrFail();

            // Check if InspectionStandard already exists
            $inspectionStandard = $requestDocument->inspectionStandard;

            if (!$inspectionStandard) {
                // Create new IS record in draft status
                $inspectionStandard = InspectionStandard::create([
                    'request_document_id' => $requestDocument->id,
                    'supplier_id' => $requestDocument->documentRequest->supplier_id,
                    'status' => 'DRAFT',
                ]);
            }

            // Get supplier data for auto-population
            $supplier = $requestDocument->documentRequest->supplier;
            $documentRequest = $requestDocument->documentRequest;

            return response()->json([
                'success' => true,
                'data' => [
                    'inspectionStandardId' => $inspectionStandard->id,
                    'requestDocumentId' => $requestDocument->id,
                    'status' => $inspectionStandard->status,
                    'generalInformation' => [
                        'model' => $inspectionStandard->model,
                        'dateIssued' => $inspectionStandard->date_issued?->format('Y-m-d'),
                        'refNo' => $inspectionStandard->ref_no,
                        'partRoute' => $inspectionStandard->part_route,
                        'partNo' => $inspectionStandard->part_no,
                        'partName' => $inspectionStandard->part_name,
                        'manufacturer' => $inspectionStandard->manufacturer,
                        'material' => $inspectionStandard->material,
                        'generalTolerance' => $inspectionStandard->general_tolerance,
                        'weight' => $inspectionStandard->weight,
                        'revisionDate' => $inspectionStandard->revision_date?->format('Y-m-d'),
                        'revisionRecord' => $inspectionStandard->revision_record,
                    ],
                    'supplierInfo' => [
                        'supplier' => $supplier->name ?? '',
                        'customer' => 'AAIJ (Purchasing & Quality Division)',
                    ],
                    'drawings' => $inspectionStandard->drawings->map(fn($d) => [
                        'id' => $d->id,
                        'drawingName' => $d->drawing_name,
                        'drawingNo' => $d->drawing_no,
                        'revision' => $d->revision,
                        'description' => $d->description,
                        'filePath' => $d->file_path,
                        'originalFilename' => $d->original_filename,
                        'fileSize' => $d->file_size,
                        'fileType' => $d->file_type,
                    ])->toArray(),
                    'inspectionItems' => $inspectionStandard->inspectionItems->map(fn($i) => [
                        'id' => $i->id,
                        'inspectionItem' => $i->inspection_item,
                        'inspectionInstrument' => $i->inspection_instrument,
                        'inspectionCriterion' => $i->inspection_criterion,
                        'rank' => $i->rank,
                        'supplierSampling' => $i->supplier_sampling,
                        'aaijSampling' => $i->aaij_sampling,
                        'remarkInspectionMethod' => $i->remark_inspection_method,
                    ])->toArray(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting IS form', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Save form data as draft
     * POST /api/inspection-standards/{inspectionStandardId}/draft
     */
    public function saveDraft($inspectionStandardId, Request $request)
    {
        DB::beginTransaction();

        try {
            $inspectionStandard = InspectionStandard::findOrFail($inspectionStandardId);

            Log::info('[IS Draft] Starting save draft', [
                'inspection_standard_id' => $inspectionStandardId,
                'has_general_info' => $request->has('generalInformation'),
                'has_drawings' => $request->has('drawings'),
                'has_inspection_items' => $request->has('inspectionItems'),
            ]);

            // Update general information
            $generalInfo = $request->input('generalInformation', []);
            $inspectionStandard->update([
                'model' => $generalInfo['model'] ?? null,
                'date_issued' => $generalInfo['dateIssued'] ? date('Y-m-d', strtotime($generalInfo['dateIssued'])) : null,
                'ref_no' => $generalInfo['refNo'] ?? null,
                'part_route' => $generalInfo['partRoute'] ?? null,
                'part_no' => $generalInfo['partNo'] ?? null,
                'part_name' => $generalInfo['partName'] ?? null,
                'manufacturer' => $generalInfo['manufacturer'] ?? null,
                'material' => $generalInfo['material'] ?? null,
                'general_tolerance' => $generalInfo['generalTolerance'] ?? null,
                'weight' => $generalInfo['weight'] ?? null,
                'revision_date' => $generalInfo['revisionDate'] ? date('Y-m-d', strtotime($generalInfo['revisionDate'])) : null,
                'revision_record' => $generalInfo['revisionRecord'] ?? null,
            ]);

            Log::info('[IS Draft] General information updated');

            // Update drawings
            $drawings = $request->input('drawings', []);
            
            // Get IDs from request to determine which ones to keep
            $drawingIdsFromRequest = collect($drawings)
                ->filter(fn($drawing) => isset($drawing['id']) && is_numeric($drawing['id']))
                ->pluck('id')
                ->toArray();
            
            // Delete drawings that are not in the request anymore
            if (!empty($drawingIdsFromRequest)) {
                InspectionStandardDrawing::where('inspection_standard_id', $inspectionStandard->id)
                    ->whereNotIn('id', $drawingIdsFromRequest)
                    ->delete();
            }
            
            // Update or create drawings from request
            foreach ($drawings as $index => $drawingData) {
                if (isset($drawingData['id']) && is_numeric($drawingData['id'])) {
                    // Update existing drawing
                    InspectionStandardDrawing::where('id', $drawingData['id'])
                        ->where('inspection_standard_id', $inspectionStandard->id)
                        ->update([
                            'drawing_name' => $drawingData['drawingName'] ?? null,
                            'drawing_no' => $drawingData['drawingNo'] ?? null,
                            'revision' => $drawingData['revision'] ?? null,
                            'description' => $drawingData['description'] ?? null,
                            'sequence_number' => $index,
                        ]);
                }
            }

            Log::info('[IS Draft] Drawings updated', [
                'count' => count($drawings)
            ]);

            // Update inspection items
            $inspectionItems = $request->input('inspectionItems', []);
            
            Log::info('[IS Draft] Processing inspection items', [
                'count' => count($inspectionItems),
                'items' => $inspectionItems
            ]);
            
            // Separate existing items (numeric ID) from new items (temp ID or no ID)
            $existingItemIds = [];
            $itemsToUpdate = [];
            $itemsToCreate = [];
            
            foreach ($inspectionItems as $index => $itemData) {
                $itemId = $itemData['id'] ?? null;
                
                // Check if ID is numeric (existing item) or temp/null (new item)
                if ($itemId && is_numeric($itemId)) {
                    $existingItemIds[] = (int)$itemId;
                    $itemsToUpdate[] = [
                        'id' => (int)$itemId,
                        'index' => $index,
                        'data' => $itemData
                    ];
                } else {
                    $itemsToCreate[] = [
                        'index' => $index,
                        'data' => $itemData
                    ];
                }
            }
            
            Log::info('[IS Draft] Items categorized', [
                'existing_count' => count($existingItemIds),
                'to_update' => count($itemsToUpdate),
                'to_create' => count($itemsToCreate)
            ]);
            
            // Delete items that are not in the request anymore (only for existing items)
            if (!empty($existingItemIds)) {
                $deleted = InspectionStandardItem::where('inspection_standard_id', $inspectionStandard->id)
                    ->whereNotIn('id', $existingItemIds)
                    ->delete();
                Log::info('[IS Draft] Deleted old items', ['count' => $deleted]);
            } else {
                // If no existing IDs, delete all items (user is starting fresh)
                $deleted = InspectionStandardItem::where('inspection_standard_id', $inspectionStandard->id)->delete();
                Log::info('[IS Draft] Deleted all items (fresh start)', ['count' => $deleted]);
            }
            
            // Update existing items
            foreach ($itemsToUpdate as $itemInfo) {
                InspectionStandardItem::where('id', $itemInfo['id'])
                    ->where('inspection_standard_id', $inspectionStandard->id)
                    ->update([
                        'inspection_item' => $itemInfo['data']['inspectionItem'] ?? null,
                        'inspection_instrument' => $itemInfo['data']['inspectionInstrument'] ?? null,
                        'inspection_criterion' => $itemInfo['data']['inspectionCriterion'] ?? null,
                        'rank' => $itemInfo['data']['rank'] ?? null,
                        'supplier_sampling' => $itemInfo['data']['supplierSampling'] ?? null,
                        'aaij_sampling' => $itemInfo['data']['aaijSampling'] ?? null,
                        'remark_inspection_method' => $itemInfo['data']['remarkInspectionMethod'] ?? null,
                        'sequence_number' => $itemInfo['index'],
                    ]);
            }
            
            Log::info('[IS Draft] Updated existing items', ['count' => count($itemsToUpdate)]);
            
            // Create new items
            foreach ($itemsToCreate as $itemInfo) {
                InspectionStandardItem::create([
                    'inspection_standard_id' => $inspectionStandard->id,
                    'inspection_item' => $itemInfo['data']['inspectionItem'] ?? null,
                    'inspection_instrument' => $itemInfo['data']['inspectionInstrument'] ?? null,
                    'inspection_criterion' => $itemInfo['data']['inspectionCriterion'] ?? null,
                    'rank' => $itemInfo['data']['rank'] ?? null,
                    'supplier_sampling' => $itemInfo['data']['supplierSampling'] ?? null,
                    'aaij_sampling' => $itemInfo['data']['aaijSampling'] ?? null,
                    'remark_inspection_method' => $itemInfo['data']['remarkInspectionMethod'] ?? null,
                    'sequence_number' => $itemInfo['index'],
                ]);
            }
            
            Log::info('[IS Draft] Created new items', ['count' => count($itemsToCreate)]);

            DB::commit();

            Log::info('[IS Draft] Save draft completed successfully');

            return response()->json([
                'success' => true,
                'message' => 'Form saved as draft',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[IS Draft] Error saving draft', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error saving draft: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Upload drawing file
     * POST /api/inspection-standards/{inspectionStandardId}/upload-drawing
     */
    public function uploadDrawing($inspectionStandardId, Request $request)
    {
        Log::info('Upload drawing attempt', [
            'inspection_standard_id' => $inspectionStandardId,
            'has_file' => $request->hasFile('file'),
            'files' => $request->allFiles(),
            'all_input' => $request->all(),
        ]);
        
        try {
            $request->validate([
                'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240', // 10MB
            ]);

            $inspectionStandard = InspectionStandard::findOrFail($inspectionStandardId);
            $file = $request->file('file');

            // Store file
            $filename = uniqid() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs(
                'inspection-standards/' . $inspectionStandard->id,
                $filename,
                'documents'
            );

            // Create drawing record
            $drawing = InspectionStandardDrawing::create([
                'inspection_standard_id' => $inspectionStandard->id,
                'file_path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'drawing_name' => $request->input('drawing_name'),
                'drawing_no' => $request->input('drawing_no'),
                'revision' => $request->input('revision'),
                'description' => $request->input('description'),
                'sequence_number' => InspectionStandardDrawing::where('inspection_standard_id', $inspectionStandard->id)->count(),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $drawing->id,
                    'file_path' => $drawing->file_path,
                    'original_filename' => $drawing->original_filename,
                    'file_type' => $drawing->file_type,
                    'file_size' => $drawing->file_size,
                    'drawing_name' => $drawing->drawing_name,
                    'drawing_no' => $drawing->drawing_no,
                    'revision' => $drawing->revision,
                    'description' => $drawing->description,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error uploading drawing', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Delete drawing
     * DELETE /api/inspection-standards/{inspectionStandardId}/drawings/{drawingId}
     */
    public function deleteDrawing($inspectionStandardId, $drawingId)
    {
        try {
            $drawing = InspectionStandardDrawing::where([
                'id' => $drawingId,
                'inspection_standard_id' => $inspectionStandardId,
            ])->firstOrFail();

            // Delete file from storage
            if (Storage::disk('documents')->exists($drawing->file_path)) {
                Storage::disk('documents')->delete($drawing->file_path);
            }

            // Delete record
            $drawing->delete();

            return response()->json([
                'success' => true,
                'message' => 'Drawing deleted',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting drawing', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Submit Inspection Standard
     * POST /api/inspection-standards/{inspectionStandardId}/submit
     */
    public function submit($inspectionStandardId, Request $request)
    {
        DB::beginTransaction();

        try {
            Log::info('[IS Submit] Starting submission', [
                'inspection_standard_id' => $inspectionStandardId
            ]);

            $inspectionStandard = InspectionStandard::with(['drawings', 'inspectionItems', 'supplier'])
                ->findOrFail($inspectionStandardId);

            // Save data directly without calling saveDraft (to avoid nested transactions)
            $this->updateInspectionStandardData($inspectionStandard, $request);

            // Re-load the inspection standard with all relations after saving
            $inspectionStandard->refresh();
            $inspectionStandard->load(['drawings', 'inspectionItems', 'supplier']);

            Log::info('[IS Submit] Data updated and reloaded', [
                'drawings_count' => $inspectionStandard->drawings->count(),
                'items_count' => $inspectionStandard->inspectionItems->count()
            ]);

            // Validate for submission
            $errors = $inspectionStandard->validateForSubmission();
            if (!empty($errors)) {
                Log::warning('[IS Submit] Validation failed', ['errors' => $errors]);
                throw new \Exception(implode(', ', $errors));
            }

            Log::info('[IS Submit] Validation passed, generating PDF...');

            // Generate PDF
            $pdfGenerator = new \App\Services\InspectionStandardPdfGenerator();
            $pdfPath = $pdfGenerator->generate($inspectionStandard);

            Log::info('[IS Submit] PDF generated', ['path' => $pdfPath]);

            // Update status to SUBMITTED
            $inspectionStandard->update([
                'status' => 'SUBMITTED',
                'submitted_at' => now(),
            ]);

            // Update related RequestDocument status
            $requestDocument = $inspectionStandard->requestDocument;
            if ($requestDocument) {
                $requestDocument->update([
                    'status' => 'SUBMITTED',
                    'submitted_at' => now(),
                ]);
                Log::info('[IS Submit] Request document status updated');
            }

            // TODO: Trigger notification job here

            DB::commit();

            Log::info('[IS Submit] Submission completed successfully');

            return response()->json([
                'success' => true,
                'message' => 'Inspection Standard submitted successfully',
                'data' => [
                    'status' => $inspectionStandard->status,
                    'submittedAt' => $inspectionStandard->submitted_at,
                    'pdfPath' => $pdfPath,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[IS Submit] Error submitting IS', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update inspection standard data (extracted from saveDraft to avoid nested transactions)
     */
    private function updateInspectionStandardData(InspectionStandard $inspectionStandard, Request $request)
    {
        // Update general information
        $generalInfo = $request->input('generalInformation', []);
        $inspectionStandard->update([
            'model' => $generalInfo['model'] ?? null,
            'date_issued' => $generalInfo['dateIssued'] ? date('Y-m-d', strtotime($generalInfo['dateIssued'])) : null,
            'ref_no' => $generalInfo['refNo'] ?? null,
            'part_route' => $generalInfo['partRoute'] ?? null,
            'part_no' => $generalInfo['partNo'] ?? null,
            'part_name' => $generalInfo['partName'] ?? null,
            'manufacturer' => $generalInfo['manufacturer'] ?? null,
            'material' => $generalInfo['material'] ?? null,
            'general_tolerance' => $generalInfo['generalTolerance'] ?? null,
            'weight' => $generalInfo['weight'] ?? null,
            'revision_date' => $generalInfo['revisionDate'] ? date('Y-m-d', strtotime($generalInfo['revisionDate'])) : null,
            'revision_record' => $generalInfo['revisionRecord'] ?? null,
        ]);

        // Update drawings
        $drawings = $request->input('drawings', []);
        $drawingIdsFromRequest = collect($drawings)
            ->filter(fn($drawing) => isset($drawing['id']) && is_numeric($drawing['id']))
            ->pluck('id')
            ->toArray();
        
        if (!empty($drawingIdsFromRequest)) {
            InspectionStandardDrawing::where('inspection_standard_id', $inspectionStandard->id)
                ->whereNotIn('id', $drawingIdsFromRequest)
                ->delete();
        }
        
        foreach ($drawings as $index => $drawingData) {
            if (isset($drawingData['id']) && is_numeric($drawingData['id'])) {
                InspectionStandardDrawing::where('id', $drawingData['id'])
                    ->where('inspection_standard_id', $inspectionStandard->id)
                    ->update([
                        'drawing_name' => $drawingData['drawingName'] ?? null,
                        'drawing_no' => $drawingData['drawingNo'] ?? null,
                        'revision' => $drawingData['revision'] ?? null,
                        'description' => $drawingData['description'] ?? null,
                        'sequence_number' => $index,
                    ]);
            }
        }

        // Update inspection items
        $inspectionItems = $request->input('inspectionItems', []);
        $existingItemIds = [];
        $itemsToUpdate = [];
        $itemsToCreate = [];
        
        foreach ($inspectionItems as $index => $itemData) {
            $itemId = $itemData['id'] ?? null;
            
            if ($itemId && is_numeric($itemId)) {
                $existingItemIds[] = (int)$itemId;
                $itemsToUpdate[] = [
                    'id' => (int)$itemId,
                    'index' => $index,
                    'data' => $itemData
                ];
            } else {
                $itemsToCreate[] = [
                    'index' => $index,
                    'data' => $itemData
                ];
            }
        }
        
        if (!empty($existingItemIds)) {
            InspectionStandardItem::where('inspection_standard_id', $inspectionStandard->id)
                ->whereNotIn('id', $existingItemIds)
                ->delete();
        } else {
            InspectionStandardItem::where('inspection_standard_id', $inspectionStandard->id)->delete();
        }
        
        foreach ($itemsToUpdate as $itemInfo) {
            InspectionStandardItem::where('id', $itemInfo['id'])
                ->where('inspection_standard_id', $inspectionStandard->id)
                ->update([
                    'inspection_item' => $itemInfo['data']['inspectionItem'] ?? null,
                    'inspection_instrument' => $itemInfo['data']['inspectionInstrument'] ?? null,
                    'inspection_criterion' => $itemInfo['data']['inspectionCriterion'] ?? null,
                    'rank' => $itemInfo['data']['rank'] ?? null,
                    'supplier_sampling' => $itemInfo['data']['supplierSampling'] ?? null,
                    'aaij_sampling' => $itemInfo['data']['aaijSampling'] ?? null,
                    'remark_inspection_method' => $itemInfo['data']['remarkInspectionMethod'] ?? null,
                    'sequence_number' => $itemInfo['index'],
                ]);
        }
        
        foreach ($itemsToCreate as $itemInfo) {
            InspectionStandardItem::create([
                'inspection_standard_id' => $inspectionStandard->id,
                'inspection_item' => $itemInfo['data']['inspectionItem'] ?? null,
                'inspection_instrument' => $itemInfo['data']['inspectionInstrument'] ?? null,
                'inspection_criterion' => $itemInfo['data']['inspectionCriterion'] ?? null,
                'rank' => $itemInfo['data']['rank'] ?? null,
                'supplier_sampling' => $itemInfo['data']['supplierSampling'] ?? null,
                'aaij_sampling' => $itemInfo['data']['aaijSampling'] ?? null,
                'remark_inspection_method' => $itemInfo['data']['remarkInspectionMethod'] ?? null,
                'sequence_number' => $itemInfo['index'],
            ]);
        }
    }

    /**
     * Get inspection standard data for review
     * GET /api/inspection-standards/{inspectionStandardId}
     */
    public function show($inspectionStandardId)
    {
        try {
            $inspectionStandard = InspectionStandard::with(['drawings', 'inspectionItems', 'supplier'])
                ->findOrFail($inspectionStandardId);

            return response()->json([
                'success' => true,
                'data' => $inspectionStandard->getFormDataArray(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting IS', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * View PDF
     * GET /inspection-standard/{inspectionStandardId}/pdf
     */
    public function viewPdf($inspectionStandardId)
    {
        try {
            $inspectionStandard = InspectionStandard::findOrFail($inspectionStandardId);

            $pdfGenerator = new \App\Services\InspectionStandardPdfGenerator();
            return $pdfGenerator->view($inspectionStandard);
        } catch (\Exception $e) {
            Log::error('Error viewing PDF', ['error' => $e->getMessage()]);
            abort(404, $e->getMessage());
        }
    }

    /**
     * Download PDF
     * GET /inspection-standard/{inspectionStandardId}/download
     */
    public function downloadPdf($inspectionStandardId)
    {
        try {
            $inspectionStandard = InspectionStandard::findOrFail($inspectionStandardId);

            $pdfGenerator = new \App\Services\InspectionStandardPdfGenerator();
            return $pdfGenerator->download($inspectionStandard);
        } catch (\Exception $e) {
            Log::error('Error downloading PDF', ['error' => $e->getMessage()]);
            abort(404, $e->getMessage());
        }
    }

    /**
     * Serve drawing file
     * GET /inspection-standard/drawings/{inspectionStandardId}/{filename}
     */
    public function serveDrawing($inspectionStandardId, $filename)
    {
        try {
            $path = 'inspection-standards/' . $inspectionStandardId . '/' . $filename;
            
            if (!Storage::disk('documents')->exists($path)) {
                abort(404, 'Drawing file not found');
            }

            $mimeType = Storage::disk('documents')->mimeType($path);
            $file = Storage::disk('documents')->get($path);

            return response($file, 200)->header('Content-Type', $mimeType);
        } catch (\Exception $e) {
            Log::error('Error serving drawing', [
                'inspection_standard_id' => $inspectionStandardId,
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);
            abort(404);
        }
    }
}
