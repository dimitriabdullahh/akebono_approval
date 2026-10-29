<?php

namespace App\Http\Controllers;

use App\Events\DocumentSubmitted;
use App\Models\PackagingStandard;
use App\Models\PackagingStandardImage;
use App\Models\RequestDocument;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class PackagingStandardController extends Controller
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Debug endpoint to check current PS state
     */
    public function debugState($psId)
    {
        $ps = PackagingStandard::find($psId);
        
        if (!$ps) {
            return response()->json(['error' => 'Not found'], 404);
        }

        // Load images
        $ps->load('images');
        
        $photoImages = $ps->imagesByType('image_of_packaging');
        $labelImages = $ps->imagesByType('part_identification_point');
        
        return response()->json([
            'id' => $ps->id,
            'document_no' => $ps->document_no,
            'date' => $ps->date?->format('Y-m-d'),
            'status' => $ps->status,
            'total_images' => $ps->images()->count(),
            'photo_images_count' => $photoImages->count(),
            'label_images_count' => $labelImages->count(),
            'photo_images' => $photoImages->map(function($img) {
                return [
                    'id' => $img->id,
                    'file_path' => $img->file_path,
                    'original_filename' => $img->original_filename,
                    'file_size' => $img->file_size,
                    'order' => $img->order,
                    'path_exists' => file_exists(storage_path('app/private/' . $img->file_path)) || 
                                    file_exists(storage_path('app/' . $img->file_path)) ||
                                    file_exists(public_path('storage/' . $img->file_path))
                ];
            }),
            'label_images' => $labelImages->map(function($img) {
                return [
                    'id' => $img->id,
                    'file_path' => $img->file_path,
                    'original_filename' => $img->original_filename,
                    'file_size' => $img->file_size,
                    'order' => $img->order,
                    'path_exists' => file_exists(storage_path('app/private/' . $img->file_path)) || 
                                    file_exists(storage_path('app/' . $img->file_path)) ||
                                    file_exists(public_path('storage/' . $img->file_path))
                ];
            }),
            'validation_errors' => $ps->validateForSubmission(),
        ]);
    }

    public function showForm($token)
    {
        $requestDocument = RequestDocument::where('id', $token)
            ->with(['documentRequest', 'documentRequest.supplier', 'requestPart'])
            ->first();

        if (!$requestDocument) {
            abort(404, 'Document request tidak ditemukan');
        }

        // Get or create PackagingStandard record
        $ps = PackagingStandard::where('request_document_id', $requestDocument->id)->first();

        if (!$ps) {
            // Create new PackagingStandard record if not exists
            $ps = new PackagingStandard([
                'request_document_id' => $requestDocument->id,
                'supplier_id' => $requestDocument->documentRequest->supplier_id,
                'status' => 'DRAFT',
                'customer' => 'PT. AKEBONO BRAKE ASTRA INDONESIA',
                'part_no' => $requestDocument->requestPart->part_number ?? '',
                'part_name' => $requestDocument->requestPart->part_name ?? '',
            ]);
            $ps->save();
        }

        return view('packaging-standard.form', [
            'ps' => $ps,
            'requestDocument' => $requestDocument,
            'token' => $token,
        ]);
    }

    /**
     * Get form data (API endpoint for AJAX)
     */
    public function getForm($token)
    {
        $requestDocument = RequestDocument::where('id', $token)
            ->with(['requestPart'])
            ->first();

        if (!$requestDocument) {
            return response()->json(['error' => 'Document not found'], 404);
        }

        $ps = PackagingStandard::where('request_document_id', $requestDocument->id)
            ->first();

        if (!$ps) {
            return response()->json(['error' => 'Packaging Standard not found'], 404);
        }

        $data = $ps->getFormDataArray();
        
        // Fallback to requestPart data if database fields are empty
        if (empty($data['part_no']) && $requestDocument->requestPart) {
            $data['part_no'] = $requestDocument->requestPart->part_number;
        }
        if (empty($data['part_name']) && $requestDocument->requestPart) {
            $data['part_name'] = $requestDocument->requestPart->part_name;
        }

        // Load images by type
        $data['packaging_images'] = $ps->imagesByType('image_of_packaging')->toArray();
        $data['identification_images'] = $ps->imagesByType('part_identification_point')->toArray();

        return response()->json($data);
    }

    /**
     * Save draft (auto-save)
     */
    public function saveDraft($psId, Request $request)
    {
        $ps = PackagingStandard::with('requestDocument')->find($psId);

        if (!$ps) {
            return response()->json(['error' => 'Packaging Standard not found'], 404);
        }

        // Validasi: pastikan packaging_standard ini benar-benar milik request_document yang valid
        if (!$ps->requestDocument) {
            return response()->json(['error' => 'Invalid request document association'], 400);
        }

        try {
            \Log::info('PS saveDraft called', [
                'ps_id' => $psId,
                'request_document_id' => $ps->request_document_id,
                'has_company_logo' => $request->has('company_logo'),
                'company_logo_length' => $request->has('company_logo') ? strlen($request->input('company_logo')) : 0
            ]);
            
            $data = $request->validate([
                'document_no' => 'nullable|string|max:255',
                'revision' => 'nullable|string|max:50',
                'date' => 'nullable|date_format:Y-m-d',
                'part_no' => 'nullable|string|max:255',
                'part_name' => 'nullable|string|max:255',
                'packaging_type' => 'nullable|string|max:100',
                'packaging_size_mm' => 'nullable|string|max:255',
                'packaging_lot_pcs_box' => 'nullable|integer|min:1',
                'net_weight_g_pc' => 'nullable|numeric',
                'total_weight_kg_box' => 'nullable|numeric',
                'materials' => 'nullable|array',
                'materials.*.part_no' => 'nullable|string|max:255',
                'materials.*.part_name' => 'nullable|string|max:255',
                'materials.*.supplier' => 'nullable|string|max:255',
                'materials.*.qty' => 'nullable|integer|min:0',
                'company_logo' => 'nullable|string|max:5000000', // 5MB in base64
                // Approval QR fields
                'prepared_by_name' => 'nullable|string|max:255',
                'checked_by_name' => 'nullable|string|max:255',
                'approved_by_name' => 'nullable|string|max:255',
            ]);

            // Extract materials and company_logo
            $materials = $data['materials'] ?? [];
            $companyLogo = $data['company_logo'] ?? null;
            unset($data['materials']);
            // Don't unset company_logo from $data yet

            // Always set customer from system (not from user input)
            // Part No and Part Name are read-only and set from documentRequest
            $data['customer'] = 'PT. AKEBONO BRAKE ASTRA INDONESIA';
            // Update status menjadi DRAFT karena user sudah mulai mengisi
            $data['status'] = 'DRAFT';
            
            // Set company logo directly in data
            if ($companyLogo) {
                \Log::info('PS saveDraft: Setting company logo in data', [
                    'ps_id' => $psId,
                    'logo_length' => strlen($companyLogo)
                ]);
                $data['company_logo'] = $companyLogo;
            }

            // Save materials to form_data_json first
            $formDataJson = $ps->form_data_json ?? [];
            $formDataJson['materials'] = $materials;  // Always save materials array (even if empty)
            $data['form_data_json'] = $formDataJson;

            // Update PackagingStandard record (includes company_logo and form_data_json now)
            $ps->update($data);

            // TIDAK perlu update status RequestDocument karena:
            // - RequestDocument.status digunakan untuk tracking approval workflow
            // - PackagingStandard.status digunakan untuk tracking pengisian form
            // - Keduanya independen
            
            \Log::info('PS saveDraft: Saved successfully', [
                'ps_id' => $psId,
                'has_logo_after_save' => !empty($ps->fresh()->company_logo)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Draft saved successfully',
                'ps' => $ps,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving draft: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Upload image for packaging standard
     */
    public function uploadImage($psId, Request $request)
    {
        $ps = PackagingStandard::find($psId);

        if (!$ps) {
            return response()->json(['error' => 'Packaging Standard not found'], 404);
        }

        $request->validate([
            'image_type' => 'required|in:photographic,part_identification,visualisasi_packing,label_packing',
            'image' => 'required|file|mimes:jpg,jpeg,png|max:10000',
        ]);

        try {
            $imageType = $request->input('image_type');
            $fieldName = $this->getImageFieldName($imageType);

            if (!$fieldName) {
                return response()->json(['error' => 'Invalid image type'], 422);
            }

            if ($request->hasFile('image')) {
                // Delete old image if exists
                $oldPath = $ps->$fieldName;
                if ($oldPath && Storage::exists($oldPath)) {
                    Storage::delete($oldPath);
                }

                // Store new image
                $path = $request->file('image')->store('packaging-standard/images', 'private');
                $ps->update([$fieldName => $path]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'field' => $fieldName,
                'path' => $ps->$fieldName,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error uploading image: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete image from packaging standard
     */
    public function deleteImage($psId, Request $request)
    {
        $ps = PackagingStandard::find($psId);

        if (!$ps) {
            return response()->json(['error' => 'Packaging Standard not found'], 404);
        }

        $request->validate([
            'image_type' => 'required|in:photographic,part_identification,visualisasi_packing,label_packing',
        ]);

        try {
            $imageType = $request->input('image_type');
            $fieldName = $this->getImageFieldName($imageType);

            if (!$fieldName) {
                return response()->json(['error' => 'Invalid image type'], 422);
            }

            $path = $ps->$fieldName;
            if ($path && Storage::exists($path)) {
                Storage::delete($path);
                $ps->update([$fieldName => null]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully',
                'field' => $fieldName,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting image: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Submit the form
     */
    public function submit($psId, Request $request)
    {
        $ps = PackagingStandard::find($psId);

        if (!$ps) {
            return response()->json(['error' => 'Packaging Standard not found'], 404);
        }

        try {
            $data = $request->validate([
                'document_no' => 'required|string|max:255',
                'date' => 'required|date_format:Y-m-d',
                'part_no' => 'nullable|string|max:255',
                'part_name' => 'nullable|string|max:255',
                'packaging_type' => 'required|string|max:100',
                'packaging_size_mm' => 'required|string|max:255',
                'packaging_lot_pcs_box' => 'required|integer|min:1',
                'net_weight_g_pc' => 'nullable|numeric',
                'total_weight_kg_box' => 'nullable|numeric',
                'materials' => 'nullable|array',
                'materials.*.part_no' => 'nullable|string|max:255',
                'materials.*.part_name' => 'nullable|string|max:255',
                'materials.*.supplier' => 'nullable|string|max:255',
                'materials.*.qty' => 'nullable|integer|min:0',
                'image_of_packaging' => 'nullable|array',
                'image_of_packaging.*.name' => 'nullable|string',
                'image_of_packaging.*.data' => 'nullable|string',
                'image_of_packaging.*.size' => 'nullable|integer',
                'part_identification_point' => 'nullable|array',
                'part_identification_point.*.name' => 'nullable|string',
                'part_identification_point.*.data' => 'nullable|string',
                'part_identification_point.*.size' => 'nullable|integer',
                'company_logo' => 'nullable|string|max:5000000',
                // Approval QR fields
                'prepared_by_name' => 'nullable|string|max:255',
                'checked_by_name' => 'nullable|string|max:255',
                'approved_by_name' => 'nullable|string|max:255',
            ]);

            $packagingImages = $request->input('image_of_packaging', []);
            $identificationImages = $request->input('part_identification_point', []);
            $companyLogo = $request->input('company_logo', null);
            
            // Remove image arrays from data (they will be saved separately)
            // But keep materials in data - will be saved to form_data_json
            unset($data['image_of_packaging'], $data['part_identification_point']);
            
            $data['customer'] = 'PT. AKEBONO BRAKE ASTRA INDONESIA';
            
            if ($companyLogo) {
                $data['company_logo'] = $companyLogo;
            }
            
            // Save materials to form_data_json first
            $materials = $data['materials'] ?? [];
            $formDataJson = $ps->form_data_json ?? [];
            $formDataJson['materials'] = $materials;  // Always save materials array (even if empty)
            $data['form_data_json'] = $formDataJson;
            
            // VALIDATE BEFORE SAVING - create temporary model to validate
            $tempPs = clone $ps;
            foreach ($data as $key => $value) {
                if ($key !== 'materials') {  // materials is in form_data_json
                    $tempPs->$key = $value;
                }
            }
            
            $errors = $tempPs->validateForSubmission();
            if (!empty($errors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(', ', $errors),
                    'errors' => $errors,
                ], 422);
            }
            
            // Generate QR codes for approval signatures
            $preparedQr = \App\Services\QrCodeService::generateApprovalQR(
                $data['prepared_by_name'] ?? null,
                null, // NPK tidak dipakai lagi
                null  // Jabatan tidak dipakai lagi
            );
            
            $checkedQr = \App\Services\QrCodeService::generateApprovalQR(
                $data['checked_by_name'] ?? null,
                null, // NPK tidak dipakai lagi
                null  // Jabatan tidak dipakai lagi
            );
            
            $approvedQr = \App\Services\QrCodeService::generateApprovalQR(
                $data['approved_by_name'] ?? null,
                null, // NPK tidak dipakai lagi
                null  // Jabatan tidak dipakai lagi
            );
            
            // Add QR data to update array (will be null if generation failed)
            if ($preparedQr) {
                $data['prepared_qr_data'] = $preparedQr;
            }
            if ($checkedQr) {
                $data['checked_qr_data'] = $checkedQr;
            }
            if ($approvedQr) {
                $data['approved_qr_data'] = $approvedQr;
            }
            
            \Log::info('[PackagingStandard] QR codes generated', [
                'prepared' => $preparedQr ? 'YES' : 'NO',
                'checked' => $checkedQr ? 'YES' : 'NO',
                'approved' => $approvedQr ? 'YES' : 'NO',
            ]);
            
            // NOW save all data including materials and QR codes
            $ps->update($data);

            // Save images to PackagingStandardImage table
            if (!empty($packagingImages) && is_array($packagingImages)) {
                foreach ($packagingImages as $index => $imageData) {
                    if (is_array($imageData) && isset($imageData['data'])) {
                        $this->savePackagingImage($ps, $imageData, 'image_of_packaging', $index);
                    } elseif (is_string($imageData) && !empty($imageData)) {
                        $this->savePackagingImage($ps, [
                            'data' => $imageData,
                            'name' => 'image_' . $index . '.png',
                            'size' => strlen($imageData)
                        ], 'image_of_packaging', $index);
                    }
                }
            }

            if (!empty($identificationImages) && is_array($identificationImages)) {
                foreach ($identificationImages as $index => $imageData) {
                    if (is_array($imageData) && isset($imageData['data'])) {
                        $this->savePackagingImage($ps, $imageData, 'part_identification_point', $index);
                    } elseif (is_string($imageData) && !empty($imageData)) {
                        $this->savePackagingImage($ps, [
                            'data' => $imageData,
                            'name' => 'identification_' . $index . '.png',
                            'size' => strlen($imageData)
                        ], 'part_identification_point', $index);
                    }
                }
            }

            // Mark as submitted and update status
            $ps->update([
                'status' => 'SUBMITTED',
                'submitted_at' => now(),
            ]);

            $requestDocument = RequestDocument::find($ps->request_document_id);
            if ($requestDocument) {
                $isRevision = $requestDocument->status === 'REVISE';
                
                if ($isRevision) {
                    $requestDocument->resubmit('form', 'form');
                } else {
                    $requestDocument->update([
                        'status' => 'SUBMITTED',
                        'file_path' => 'form',
                        'pdf_path' => 'form',
                    ]);
                }
                
                $requestDocument->refresh();
                
                try {
                    DocumentSubmitted::dispatch($requestDocument, 'PACKAGING_STANDARD');
                } catch (\Exception $e) {
                    // Ignore email errors
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Form submitted successfully',
                'redirect' => route('supplier.dashboard'),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorDetails = [];
            foreach ($e->errors() as $field => $messages) {
                $errorDetails[$field] = $messages[0] ?? 'Validation failed';
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $errorDetails,
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error submitting form: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Save a single packaging image to database
     */
    private function savePackagingImage($ps, $imageData, $imageType, $order)
    {
        try {
            if (!is_array($imageData)) {
                \Log::warning("Invalid image data format for $imageType: not an array");
                return;
            }
            
            // Extract base64 data
            $base64 = $imageData['data'] ?? null;
            if (!$base64) {
                \Log::warning("No data field in image data for $imageType at index $order");
                return;
            }
            
            $name = $imageData['name'] ?? 'image_' . time() . '.png';
            $size = $imageData['size'] ?? 0;

            // Decode base64 and save to file system instead of database
            // This avoids database column size limits
            $base64String = $base64;
            
            // Remove data:image/...;base64, prefix if present
            if (strpos($base64String, 'data:image') === 0) {
                $base64String = substr($base64String, strpos($base64String, ',') + 1);
            }
            
            // Decode base64
            $imageContent = base64_decode($base64String, true);
            if (!$imageContent) {
                \Log::warning("Failed to decode base64 for $imageType at index $order");
                return;
            }
            
            // Determine extension from image data or name
            $extension = pathinfo($name, PATHINFO_EXTENSION) ?: 'png';
            
            // Create storage path
            $storagePath = 'packaging-standard/images/' . $ps->id . '/' . $imageType;
            $filename = 'image_' . $order . '_' . time() . '.' . $extension;
            $fullPath = $storagePath . '/' . $filename;
            
            // Store file
            Storage::disk('private')->put($fullPath, $imageContent);
            
            // Save reference to database (file path, not base64)
            PackagingStandardImage::create([
                'packaging_standard_id' => $ps->id,
                'image_type' => $imageType,
                'file_path' => $fullPath,
                'original_filename' => $name,
                'file_size' => strlen($imageContent),
                'order' => $order,
            ]);

            \Log::info("Saved $imageType image for PS #{$ps->id}", [
                'name' => $name,
                'order' => $order,
                'path' => $fullPath,
                'size' => strlen($imageContent)
            ]);
        } catch (\Exception $e) {
            \Log::error("Error saving packaging image: " . $e->getMessage(), [
                'imageType' => $imageType,
                'order' => $order,
                'ps_id' => $ps->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * View Packaging Standard as PDF (for admin review)
     */
    public function viewPdf($psId)
    {
        $ps = PackagingStandard::find($psId);

        if (!$ps) {
            abort(404, 'Packaging Standard not found');
        }

        // Load related data including images
        $ps->load(['supplier', 'requestDocument', 'images']);

        // Generate PDF using template
        $pdf = Pdf::loadView('packaging-standard.pdf.template', [
            'ps' => $ps,
            'formData' => $ps->getFormDataForPdf(),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('PS-' . $ps->part_no . '.pdf');
    }

    /**
     * Download Packaging Standard as PDF
     */
    public function downloadPdf($psId)
    {
        $ps = PackagingStandard::find($psId);

        if (!$ps) {
            abort(404);
        }

        // Load related data including images
        $ps->load(['supplier', 'requestDocument', 'images']);

        // Generate PDF using template
        $pdf = Pdf::loadView('packaging-standard.pdf.template', [
            'ps' => $ps,
            'formData' => $ps->getFormDataForPdf(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('PS-' . $ps->part_no . '-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Admin preview Packaging Standard form
     */
    public function adminPreview($psId)
    {
        $ps = PackagingStandard::find($psId);

        if (!$ps) {
            abort(404, 'Packaging Standard tidak ditemukan');
        }

        // Load related data
        $ps->load(['supplier', 'requestDocument.documentRequest.supplier']);

        return view('admin.forms.ps-preview', [
            'ps' => $ps,
        ]);
    }

    /**
     * Admin view Packaging Standard as PDF
     */
    public function adminViewPdf($psId)
    {
        $ps = PackagingStandard::find($psId);

        if (!$ps) {
            abort(404);
        }

        // Load related data including images and signatures
        $ps->load(['supplier', 'requestDocument.checkerEsignature', 'requestDocument.approverEsignature', 'images']);

        // Get signatures
        $checkerSignature = $ps->requestDocument?->checkerEsignature;
        $approverSignature = $ps->requestDocument?->approverEsignature;

        // Instantiate QR service
        $qrService = app(\App\Services\SignatureQrService::class);

        // Generate PDF using template
        $pdf = Pdf::loadView('packaging-standard.pdf.template', [
            'ps' => $ps,
            'formData' => $ps->getFormDataForPdf(),
            'checkerSignature' => $checkerSignature,
            'approverSignature' => $approverSignature,
            'qrService' => $qrService,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('PS-' . $ps->part_no . '.pdf');
    }

    /**
     * Get the database field name for image type
     */
    private function getImageFieldName(string $imageType): ?string
    {
        return match ($imageType) {
            'visualisasi_packing' => 'image_of_packaging', // new column name
            'part_identification' => 'part_identification_point', // new column name
            default => null,
        };
    }
}
