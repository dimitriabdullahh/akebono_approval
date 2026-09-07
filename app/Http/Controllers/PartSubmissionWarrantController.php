<?php

namespace App\Http\Controllers;

use App\Models\PartSubmissionWarrant;
use App\Models\RequestDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class PartSubmissionWarrantController extends Controller
{
    /**
     * Show the form for creating/editing a Part Submission Warrant
     */
    public function showForm($token)
    {
        $requestDocument = RequestDocument::where('id', $token)
            ->with(['documentRequest', 'documentRequest.supplier'])
            ->first();

        if (!$requestDocument) {
            abort(404, 'Document request tidak ditemukan');
        }

        // Get or create PSW record
        $psw = PartSubmissionWarrant::where('request_document_id', $requestDocument->id)->first();

        if (!$psw) {
            // Create new PSW record if not exists
            $psw = new PartSubmissionWarrant([
                'request_document_id' => $requestDocument->id,
                'supplier_id' => $requestDocument->documentRequest->supplier_id,
                'status' => 'DRAFT',
            ]);
            $psw->save();
        }

        return view('part-submission-warrant.form', [
            'psw' => $psw,
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

        $psw = PartSubmissionWarrant::where('request_document_id', $requestDocument->id)
            ->first();

        if (!$psw) {
            return response()->json(['error' => 'PSW not found'], 404);
        }

        return response()->json($psw->getFormDataArray());
    }

    /**
     * Save draft (auto-save)
     */
    public function saveDraft($pswId, Request $request)
    {
        $psw = PartSubmissionWarrant::find($pswId);

        if (!$psw) {
            return response()->json(['error' => 'PSW not found'], 404);
        }

        try {
            $data = $request->validate([
                'part_name' => 'nullable|string|max:255',
                'part_number' => 'nullable|string|max:255',
                'safety_government_regulation' => 'nullable|boolean',
                'engineering_drawing_change_level' => 'nullable|string|max:100',
                'engineering_drawing_change_date' => 'nullable|date_format:Y-m-d',
                'additional_engineering_changes' => 'nullable|string',
                'additional_engineering_changes_date' => 'nullable|date_format:Y-m-d',
                'shown_on_drawing_no' => 'nullable|string|max:255',
                'purchase_order_no' => 'nullable|string|max:255',
                'weight_kg' => 'nullable|numeric',
                'checking_aid_no' => 'nullable|string|max:255',
                'process_engineering_change_level' => 'nullable|string|max:100',
                'process_engineering_change_date' => 'nullable|date_format:Y-m-d',
                'supplier_name' => 'nullable|string|max:255',
                'supplier_code' => 'nullable|string|max:100',
                'street_address' => 'nullable|string|max:500',
                'city' => 'nullable|string|max:100',
                'state' => 'nullable|string|max:100',
                'zip' => 'nullable|string|max:20',
                'customer_name_division' => 'nullable|string|max:255',
                'buyer_buyer_code' => 'nullable|string|max:255',
                'application' => 'nullable|string|max:500',
                'submission_information' => 'nullable|json',
                'contains_prohibited_substances' => 'nullable|boolean',
                'plastic_has_iso_marking' => 'nullable|boolean',
                'reason_for_submission' => 'nullable|json',
                'reason_other_text' => 'nullable|string|max:500',
                'submission_results' => 'nullable|json',
                'meets_all_requirements' => 'nullable|boolean',
                'explanation_if_not_meet' => 'nullable|string',
                'explanation_comments' => 'nullable|string',
                'print_name' => 'nullable|string|max:255',
                'title' => 'nullable|string|max:255',
                'phone_no' => 'nullable|string|max:20',
                'fax_no' => 'nullable|string|max:20',
            ]);

            // Convert boolean fields
            $data = $this->convertBooleanFields($data);

            // Update PSW record
            $psw->update($data);

            // Store JSON representation for reference
            $psw->update(['form_data_json' => json_encode($psw->getFormDataArray())]);

            return response()->json([
                'success' => true,
                'message' => 'Draft saved successfully',
                'psw' => $psw,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving draft: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Handle signature upload
     */
    public function uploadSignature($pswId, Request $request)
    {
        $psw = PartSubmissionWarrant::find($pswId);

        if (!$psw) {
            return response()->json(['error' => 'PSW not found'], 404);
        }

        $request->validate([
            'signature' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5000',
        ]);

        try {
            if ($request->hasFile('signature')) {
                // Delete old signature if exists
                if ($psw->supplier_authorized_signature && Storage::exists($psw->supplier_authorized_signature)) {
                    Storage::delete($psw->supplier_authorized_signature);
                }

                // Store new signature
                $path = $request->file('signature')->store('psw/signatures', 'private');
                $psw->update(['supplier_authorized_signature' => $path]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Signature uploaded successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error uploading signature: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Submit the form
     */
    public function submit($pswId, Request $request)
    {
        $psw = PartSubmissionWarrant::find($pswId);

        if (!$psw) {
            return response()->json(['error' => 'PSW not found'], 404);
        }

        try {
            // First, save any form data from the request before validation
            $data = $request->validate([
                'part_name' => 'nullable|string|max:255',
                'part_number' => 'nullable|string|max:255',
                'safety_government_regulation' => 'nullable|boolean',
                'engineering_drawing_change_level' => 'nullable|string|max:100',
                'engineering_drawing_change_date' => 'nullable|date_format:Y-m-d',
                'additional_engineering_changes' => 'nullable|string',
                'additional_engineering_changes_date' => 'nullable|date_format:Y-m-d',
                'shown_on_drawing_no' => 'nullable|string|max:255',
                'purchase_order_no' => 'nullable|string|max:255',
                'weight_kg' => 'nullable|numeric',
                'checking_aid_no' => 'nullable|string|max:255',
                'process_engineering_change_level' => 'nullable|string|max:100',
                'process_engineering_change_date' => 'nullable|date_format:Y-m-d',
                'supplier_name' => 'nullable|string|max:255',
                'supplier_code' => 'nullable|string|max:100',
                'street_address' => 'nullable|string|max:500',
                'city' => 'nullable|string|max:100',
                'state' => 'nullable|string|max:100',
                'zip' => 'nullable|string|max:20',
                'customer_name_division' => 'nullable|string|max:255',
                'buyer_buyer_code' => 'nullable|string|max:255',
                'application' => 'nullable|string|max:500',
                'submission_information' => 'nullable|json',
                'contains_prohibited_substances' => 'nullable|boolean',
                'plastic_has_iso_marking' => 'nullable|boolean',
                'reason_for_submission' => 'nullable|json',
                'reason_other_text' => 'nullable|string|max:500',
                'submission_results' => 'nullable|json',
                'meets_all_requirements' => 'nullable|boolean',
                'explanation_if_not_meet' => 'nullable|string',
                'explanation_comments' => 'nullable|string',
                'print_name' => 'nullable|string|max:255',
                'title' => 'nullable|string|max:255',
                'phone_no' => 'nullable|string|max:20',
                'fax_no' => 'nullable|string|max:20',
                'supplier_authorized_signature' => 'nullable|string',
                'signature_date' => 'nullable|date_format:Y-m-d',
            ]);

            // Convert boolean fields
            $data = $this->convertBooleanFields($data);

            // Update PSW record with latest form data
            $psw->update($data);

            // Validate mandatory fields after data is saved
            $errors = $psw->validateForSubmission();

            if (!empty($errors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $errors,
                ], 422);
            }

            // Update status to SUBMITTED
            $psw->update([
                'status' => 'SUBMITTED',
                'submitted_at' => now(),
                'form_data_json' => json_encode($psw->getFormDataArray()),
            ]);

            // Update request document status
            $requestDocument = RequestDocument::find($psw->request_document_id);
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
     * View PSW as PDF (for admin review)
     */
    public function viewPdf($pswId)
    {
        $psw = PartSubmissionWarrant::find($pswId);

        if (!$psw) {
            abort(404);
        }

        // Load related data
        $psw->load(['supplier', 'requestDocument']);

        // Generate PDF using template
        $pdf = Pdf::loadView('part-submission-warrant.pdf.template', [
            'psw' => $psw,
            'formData' => $psw->getFormDataArray(),
        ]);

        return $pdf->stream('PSW-' . $psw->part_number . '.pdf');
    }

    /**
     * Download PSW as PDF
     */
    public function downloadPdf($pswId)
    {
        $psw = PartSubmissionWarrant::find($pswId);

        if (!$psw) {
            abort(404);
        }

        // Load related data
        $psw->load(['supplier', 'requestDocument']);

        // Generate PDF using template
        $pdf = Pdf::loadView('part-submission-warrant.pdf.template', [
            'psw' => $psw,
            'formData' => $psw->getFormDataArray(),
        ]);

        return $pdf->download('PSW-' . $psw->part_number . '-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Convert checkbox/boolean string values to actual booleans
     */
    private function convertBooleanFields(array $data): array
    {
        $booleanFields = [
            'safety_government_regulation',
            'contains_prohibited_substances',
            'plastic_has_iso_marking',
            'meets_all_requirements',
        ];

        foreach ($booleanFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = filter_var($data[$field], FILTER_VALIDATE_BOOLEAN);
            }
        }

        return $data;
    }
}
