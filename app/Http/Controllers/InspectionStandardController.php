<?php

namespace App\Http\Controllers;

use App\Events\DocumentSubmitted;
use App\Models\InspectionStandard;
use App\Models\InspectionStandardDrawing;
use App\Models\InspectionStandardItem;
use App\Models\RequestDocument;
use App\Models\Supplier;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class InspectionStandardController extends Controller
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
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
     * GET /api/is/form/{token}
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
            ->with(['documentRequest.supplier', 'requestPart', 'inspectionStandard'])
            ->firstOrFail();

            // Get InspectionStandard (should already exist from Observer)
            $inspectionStandard = $requestDocument->inspectionStandard;

            if (!$inspectionStandard) {
                // Fallback: Create new IS record if somehow missing
                $inspectionStandard = InspectionStandard::create([
                    'request_document_id' => $requestDocument->id,
                    'supplier_id' => $requestDocument->documentRequest->supplier_id,
                    'status' => 'DRAFT',
                ]);
                
                Log::warning('IS created in getForm as fallback - should have been created by Observer', [
                    'request_document_id' => $requestDocument->id,
                ]);
            }

            // Get supplier data and part data for auto-population
            $supplier = $requestDocument->documentRequest->supplier;
            $documentRequest = $requestDocument->documentRequest;
            
            // Get part data from RequestPart (if exists)
            $requestPart = $requestDocument->requestPart;
            
            // Generate part route if not set
            $partRoute = $inspectionStandard->part_route ?: "PT.AAIJ -> ({$supplier->name}) -> PT.AAIJ";

            // Prepare response data matching the frontend structure
            $responseData = [
                'id' => $inspectionStandard->id,
                'request_document_id' => $requestDocument->id,
                'status' => $inspectionStandard->status,
                
                // Auto-populated fields from request
                'model' => $inspectionStandard->model ?: $documentRequest->model,
                'part_no' => $inspectionStandard->part_no ?: ($requestPart->part_number ?? ''),
                'part_name' => $inspectionStandard->part_name ?: ($requestPart->part_name ?? ''),
                'manufacturer' => $inspectionStandard->manufacturer ?: ($supplier->name ?? ''),
                
                // User-editable fields
                'date_issued' => $inspectionStandard->date_issued?->format('Y-m-d'),
                'ref_no' => $inspectionStandard->ref_no,
                'part_route' => $partRoute,
                'material' => $inspectionStandard->material,
                'general_tolerance' => $inspectionStandard->general_tolerance,
                'weight' => $inspectionStandard->weight,
                'revision_date' => $inspectionStandard->revision_date?->format('Y-m-d'),
                'revision_record' => $inspectionStandard->revision_record,
                
                // Related data
                'drawings' => $inspectionStandard->drawings->map(fn($d) => [
                    'id' => $d->id,
                    'drawing_name' => $d->drawing_name,
                    'drawing_no' => $d->drawing_no,
                    'revision' => $d->revision,
                    'description' => $d->description,
                    'file_path' => $d->file_path,
                    'original_filename' => $d->original_filename,
                    'file_size' => $d->file_size,
                    'file_type' => $d->file_type,
                ])->toArray(),
                
                'inspection_items' => $inspectionStandard->inspectionItems->map(fn($i) => [
                    'id' => $i->id,
                    'inspection_item' => $i->inspection_item,
                    'inspection_instrument' => $i->inspection_instrument,
                    'inspection_criterion' => $i->inspection_criterion,
                    'rank' => $i->rank,
                    'supplier_sampling' => $i->supplier_sampling,
                    'aaij_sampling' => $i->aaij_sampling,
                    'remark_inspection_method' => $i->remark_inspection_method,
                ])->toArray(),
                
                // Request document data for revision info
                'request_document' => [
                    'id' => $requestDocument->id,
                    'status' => $requestDocument->status,
                    'revised_by_team' => $requestDocument->revised_by_team,
                    'revision_note' => $requestDocument->revision_note,
                    'revised_at' => $requestDocument->revised_at?->format('Y-m-d H:i:s'),
                    'revision_count' => $requestDocument->revision_count ?? 0,
                ],
            ];

            return response()->json($responseData);
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
     * POST /api/is/{inspectionStandardId}/draft
     */
    public function saveDraft($inspectionStandardId, Request $request)
    {
        DB::beginTransaction();

        try {
            $inspectionStandard = InspectionStandard::with('requestDocument')->findOrFail($inspectionStandardId);

            // Validasi: pastikan inspection_standard ini benar-benar milik request_document yang valid
            if (!$inspectionStandard->requestDocument) {
                throw new \Exception('Invalid request document association');
            }

            Log::info('[IS Draft] Starting save draft', [
                'inspection_standard_id' => $inspectionStandardId,
                'request_document_id' => $inspectionStandard->request_document_id,
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
                // Update status menjadi DRAFT karena user sudah mulai mengisi
                'status' => 'DRAFT',
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
            } else {
                // If no existing drawings, delete all (fresh start)
                InspectionStandardDrawing::where('inspection_standard_id', $inspectionStandard->id)->delete();
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
                } elseif (!empty($drawingData['filePath'])) {
                    // Create new drawing from frontend upload with base64 file
                    // Process base64 to file
                    $filePath = $this->processBase64Drawing($inspectionStandard, $drawingData, $index);
                    
                    if ($filePath) {
                        InspectionStandardDrawing::create([
                            'inspection_standard_id' => $inspectionStandard->id,
                            'drawing_name' => $drawingData['drawingName'] ?? null,
                            'drawing_no' => $drawingData['drawingNo'] ?? null,
                            'revision' => $drawingData['revision'] ?? null,
                            'description' => $drawingData['description'] ?? null,
                            'sequence_number' => $index,
                            'file_path' => $filePath,
                            'original_filename' => $drawingData['fileName'] ?? 'drawing_' . ($index + 1) . '.png',
                            'file_type' => 'png',
                            'file_size' => strlen($drawingData['filePath'] ?? ''),
                        ]);
                    }
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

            // TIDAK perlu update status RequestDocument karena:
            // - RequestDocument.status digunakan untuk tracking approval workflow
            // - InspectionStandard.status digunakan untuk tracking pengisian form
            // - Keduanya independen

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
     * POST /api/is/{inspectionStandardId}/upload-drawing
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
     * DELETE /api/is/{inspectionStandardId}/drawings/{drawingId}
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
     * POST /api/is/{inspectionStandardId}/submit
     */
    public function submit($inspectionStandardId, Request $request)
    {
        try {
            DB::beginTransaction();

            $inspectionStandard = InspectionStandard::with(['drawings', 'inspectionItems', 'supplier'])
                ->findOrFail($inspectionStandardId);

            // Save data
            $this->updateInspectionStandardData($inspectionStandard, $request);

            // Re-load
            $inspectionStandard->refresh();
            $inspectionStandard->load(['drawings', 'inspectionItems', 'supplier']);

            // Validate
            $errors = $inspectionStandard->validateForSubmission();
            if (!empty($errors)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(', ', $errors),
                    'errors' => $errors,
                ], 422);
            }

            // Generate PDF
            try {
                $pdfGenerator = new \App\Services\InspectionStandardPdfGenerator();
                $pdfPath = $pdfGenerator->generate($inspectionStandard);
                
                $inspectionStandard->update([
                    'pdf_file_path' => $pdfPath,
                ]);
            } catch (\Exception $e) {
                // If PDF generation fails, use temp path but log the error
                \Log::error('IS PDF Generation failed during submit', [
                    'inspection_standard_id' => $inspectionStandard->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $pdfPath = 'temp/inspection-standards/temp.pdf';
            }

            // Update status
            $inspectionStandard->update([
                'status' => 'SUBMITTED',
                'submitted_at' => now(),
            ]);

            // Update RequestDocument
            $requestDocument = $inspectionStandard->requestDocument;
            if ($requestDocument) {
                $isRevision = $requestDocument->status === 'REVISE';
                
                if ($isRevision) {
                    $requestDocument->resubmit($pdfPath);
                } else {
                    $requestDocument->update([
                        'status' => 'SUBMITTED',
                        'submitted_at' => now(),
                        'file_path' => $pdfPath,
                        'pdf_path' => $pdfPath,
                    ]);
                }
                
                $requestDocument->refresh();
                
                try {
                    DocumentSubmitted::dispatch($requestDocument, 'IS');
                } catch (\Exception $e) {
                    // Ignore email errors
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Inspection Standard submitted successfully',
                'redirect' => '/supplier/dashboard',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Inspection Standard not found',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update inspection standard data (extracted from saveDraft to avoid nested transactions)
     */
    private function updateInspectionStandardData(InspectionStandard $inspectionStandard, Request $request): void
    {
        $generalInfo = $request->input('generalInformation', []);
        
        $parseDate = function($dateString) {
            if (empty($dateString)) return null;
            try {
                return \Carbon\Carbon::parse($dateString)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        };
        
        $inspectionStandard->update([
            'model' => $generalInfo['model'] ?? null,
            'date_issued' => $parseDate($generalInfo['dateIssued'] ?? null),
            'ref_no' => $generalInfo['refNo'] ?? null,
            'part_route' => $generalInfo['partRoute'] ?? null,
            'part_no' => $generalInfo['partNo'] ?? null,
            'part_name' => $generalInfo['partName'] ?? null,
            'manufacturer' => $generalInfo['manufacturer'] ?? null,
            'material' => $generalInfo['material'] ?? null,
            'general_tolerance' => $generalInfo['generalTolerance'] ?? null,
            'weight' => $generalInfo['weight'] ?? null,
        ]);

        $drawings = $request->input('drawings', []);
        
        $drawingIdsFromRequest = collect($drawings)
            ->filter(fn($drawing) => isset($drawing['id']) && is_numeric($drawing['id']))
            ->pluck('id')
            ->toArray();
        
        if (!empty($drawingIdsFromRequest)) {
            InspectionStandardDrawing::where('inspection_standard_id', $inspectionStandard->id)
                ->whereNotIn('id', $drawingIdsFromRequest)
                ->delete();
        } else {
            InspectionStandardDrawing::where('inspection_standard_id', $inspectionStandard->id)->delete();
        }
        
        foreach ($drawings as $index => $drawingData) {
            if (isset($drawingData['id']) && is_numeric($drawingData['id'])) {
                InspectionStandardDrawing::where('id', $drawingData['id'])
                    ->where('inspection_standard_id', $inspectionStandard->id)
                    ->update([
                        'drawing_name' => $drawingData['drawingName'] ?? null,
                        'drawing_no' => $drawingData['drawingNo'] ?? null,
                        'description' => $drawingData['description'] ?? null,
                        'sequence_number' => $index,
                    ]);
            } elseif (!empty($drawingData['filePath'])) {
                $filePath = $this->processBase64Drawing($inspectionStandard, $drawingData, $index);
                
                if ($filePath) {
                    InspectionStandardDrawing::create([
                        'inspection_standard_id' => $inspectionStandard->id,
                        'drawing_name' => $drawingData['drawingName'] ?? null,
                        'drawing_no' => $drawingData['drawingNo'] ?? null,
                        'description' => $drawingData['description'] ?? null,
                        'sequence_number' => $index,
                        'file_path' => $filePath,
                        'original_filename' => $drawingData['fileName'] ?? 'drawing_' . ($index + 1) . '.png',
                        'file_type' => pathinfo($filePath, PATHINFO_EXTENSION),
                        'file_size' => \Storage::disk('documents')->size($filePath),
                    ]);
                } else {
                    throw new \Exception("Failed to process drawing file");
                }
            }
        }

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
     * GET /api/is/{inspectionStandardId}
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
     * Process base64 drawing data from frontend and save to storage
     */
    private function processBase64Drawing(InspectionStandard $inspectionStandard, array $drawingData, int $index): ?string
    {
        try {
            if (empty($drawingData['filePath'])) {
                return null;
            }

            $base64Data = $drawingData['filePath'];
            
            if (strpos($base64Data, 'data:') === 0) {
                preg_match('/data:(image|application)\/(\w+);base64,(.+)/', $base64Data, $matches);
                if (empty($matches[3])) {
                    return null;
                }
                $base64Data = $matches[3];
                $extension = $matches[2];
            } else {
                $extension = 'png';
            }

            $fileData = base64_decode($base64Data, true);
            if ($fileData === false) {
                return null;
            }

            $filename = uniqid() . '_drawing_' . ($index + 1) . '.' . $extension;
            $directory = 'inspection-standards/' . $inspectionStandard->id;
            $fullPath = $directory . '/' . $filename;
            $stored = \Storage::disk('documents')->put($fullPath, $fileData);

            if (!$stored) {
                return null;
            }

            return $fullPath;
        } catch (\Exception $e) {
            return null;
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

            // If PDF not generated yet, return message
            if (!$inspectionStandard->pdf_file_path || $inspectionStandard->pdf_file_path === 'temp/inspection-standards/temp.pdf') {
                return response()->json([
                    'success' => false,
                    'message' => 'PDF belum di-generate. Silakan submit form terlebih dahulu.',
                ], 404);
            }

            $pdfGenerator = new \App\Services\InspectionStandardPdfGenerator();
            return $pdfGenerator->view($inspectionStandard);
        } catch (\Exception $e) {
            Log::error('Error viewing PDF', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
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
