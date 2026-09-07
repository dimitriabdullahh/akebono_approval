<?php

namespace App\Http\Controllers;

use App\Models\PackagingStandard;
use App\Models\RequestDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class PackagingStandardController extends Controller
{
    /**
     * Show the form for creating/editing a Packaging Standard
     */
    public function showForm($token)
    {
        $requestDocument = RequestDocument::where('id', $token)
            ->with(['documentRequest', 'documentRequest.supplier'])
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
                'customer' => 'AAIJ (Purchasing & Quality Division)',
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
        $requestDocument = RequestDocument::where('id', $token)->first();

        if (!$requestDocument) {
            return response()->json(['error' => 'Document not found'], 404);
        }

        $ps = PackagingStandard::where('request_document_id', $requestDocument->id)
            ->first();

        if (!$ps) {
            return response()->json(['error' => 'Packaging Standard not found'], 404);
        }

        return response()->json($ps->getFormDataArray());
    }

    /**
     * Save draft (auto-save)
     */
    public function saveDraft($psId, Request $request)
    {
        $ps = PackagingStandard::find($psId);

        if (!$ps) {
            return response()->json(['error' => 'Packaging Standard not found'], 404);
        }

        try {
            $data = $request->validate([
                'document_no' => 'nullable|string|max:255',
                'revision' => 'nullable|string|max:50',
                'date' => 'nullable|date_format:Y-m-d',
                'customer' => 'nullable|string|max:255',
                'part_no' => 'nullable|string|max:255',
                'part_name' => 'nullable|string|max:255',
                'supplier_name' => 'nullable|string|max:255',
                'qty' => 'nullable|integer|min:1',
                'packaging_type' => 'nullable|string|max:100',
                'packaging_size_mm' => 'nullable|string|max:255',
                'packaging_lot_pcs_box' => 'nullable|integer|min:1',
                'net_weight_g_pc' => 'nullable|numeric',
                'total_weight_kg_box' => 'nullable|numeric',
                'prepared_by_name' => 'nullable|string|max:255',
                'prepared_by_date' => 'nullable|date_format:Y-m-d',
                'checked_by_name' => 'nullable|string|max:255',
                'checked_by_date' => 'nullable|date_format:Y-m-d',
                'approved_by_name' => 'nullable|string|max:255',
                'approved_by_date' => 'nullable|date_format:Y-m-d',
            ]);

            // Update PackagingStandard record
            $ps->update($data);

            // Store JSON representation for reference
            $ps->update(['form_data_json' => json_encode($ps->getFormDataArray())]);

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
            // First, save any form data from the request before validation
            $data = $request->validate([
                'document_no' => 'nullable|string|max:255',
                'revision' => 'nullable|string|max:50',
                'date' => 'nullable|date_format:Y-m-d',
                'customer' => 'nullable|string|max:255',
                'part_no' => 'nullable|string|max:255',
                'part_name' => 'nullable|string|max:255',
                'supplier_name' => 'nullable|string|max:255',
                'qty' => 'nullable|integer|min:1',
                'packaging_type' => 'nullable|string|max:100',
                'packaging_size_mm' => 'nullable|string|max:255',
                'packaging_lot_pcs_box' => 'nullable|integer|min:1',
                'net_weight_g_pc' => 'nullable|numeric',
                'total_weight_kg_box' => 'nullable|numeric',
                'prepared_by_name' => 'nullable|string|max:255',
                'prepared_by_date' => 'nullable|date_format:Y-m-d',
                'checked_by_name' => 'nullable|string|max:255',
                'checked_by_date' => 'nullable|date_format:Y-m-d',
                'approved_by_name' => 'nullable|string|max:255',
                'approved_by_date' => 'nullable|date_format:Y-m-d',
            ]);

            // Update PackagingStandard record with latest form data
            $ps->update($data);

            // Validate mandatory fields after data is saved
            $errors = $ps->validateForSubmission();

            if (!empty($errors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $errors,
                ], 422);
            }

            // Update status to SUBMITTED
            $ps->update([
                'status' => 'SUBMITTED',
                'submitted_at' => now(),
                'form_data_json' => json_encode($ps->getFormDataArray()),
            ]);

            // Update request document status
            $requestDocument = RequestDocument::find($ps->request_document_id);
            if ($requestDocument) {
                $requestDocument->update([
                    'status' => 'SUBMITTED',
                    'file_path' => 'form', // Mark as form submission (not PDF upload)
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Form submitted successfully',
                'redirect' => route('supplier.dashboard'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error submitting form: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * View Packaging Standard as PDF (for admin review)
     */
    public function viewPdf($psId)
    {
        $ps = PackagingStandard::find($psId);

        if (!$ps) {
            abort(404);
        }

        // Load related data
        $ps->load(['supplier', 'requestDocument']);

        // Generate PDF using template
        $pdf = Pdf::loadView('packaging-standard.pdf.template', [
            'ps' => $ps,
            'formData' => $ps->getFormDataArray(),
        ]);

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

        // Load related data
        $ps->load(['supplier', 'requestDocument']);

        // Generate PDF using template
        $pdf = Pdf::loadView('packaging-standard.pdf.template', [
            'ps' => $ps,
            'formData' => $ps->getFormDataArray(),
        ]);

        return $pdf->download('PS-' . $ps->part_no . '-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Get the database field name for image type
     */
    private function getImageFieldName(string $imageType): ?string
    {
        return match ($imageType) {
            'photographic' => 'image_photographic',
            'part_identification' => 'image_part_identification',
            'visualisasi_packing' => 'image_visualisasi_packing',
            'label_packing' => 'image_label_packing',
            default => null,
        };
    }
}
