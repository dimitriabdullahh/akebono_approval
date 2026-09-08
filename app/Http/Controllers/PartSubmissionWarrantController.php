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
            // Prepare data - convert checkbox arrays to JSON
            $data = [];
            
            // Get all input
            $input = $request->all();
            
            // List of fields to include
            $fillableFields = [
                'part_name', 'part_number', 'safety_government_regulation',
                'engineering_drawing_change_level', 'engineering_drawing_change_date',
                'additional_engineering_changes', 'additional_engineering_changes_date',
                'shown_on_drawing_no', 'purchase_order_no', 'weight_kg', 'checking_aid_no',
                'process_engineering_change_level', 'process_engineering_change_date',
                'supplier_name', 'supplier_code', 'street_address', 'city', 'state', 'zip',
                'customer_name_division', 'buyer_buyer_code', 'application',
                'contains_prohibited_substances', 'plastic_has_iso_marking',
                'reason_other_text', 'meets_all_requirements', 'explanation_if_not_meet',
                'explanation_comments', 'print_name', 'title', 'phone_no', 'fax_no',
                'signature_date',
            ];
            
            // Copy regular fields
            foreach ($fillableFields as $field) {
                if (isset($input[$field])) {
                    $data[$field] = $input[$field];
                }
            }
            
            // Handle signature file upload or path
            if ($request->hasFile('signature')) {
                // Delete old signature if exists
                if ($psw->supplier_authorized_signature && Storage::exists($psw->supplier_authorized_signature)) {
                    Storage::delete($psw->supplier_authorized_signature);
                }
                // Store new signature file
                $path = $request->file('signature')->store('psw/signatures', 'private');
                $data['supplier_authorized_signature'] = $path;
            } elseif (isset($input['supplier_authorized_signature']) && !empty($input['supplier_authorized_signature'])) {
                // If signature path is provided as string (not a file upload), keep it as is
                $data['supplier_authorized_signature'] = $input['supplier_authorized_signature'];
            }
            
            // Handle JSON fields - convert arrays to JSON strings
            if (isset($input['submission_information']) && is_array($input['submission_information'])) {
                $data['submission_information'] = json_encode($input['submission_information']);
            } elseif (isset($input['submission_information'])) {
                $data['submission_information'] = $input['submission_information'];
            }
            
            if (isset($input['reason_for_submission']) && is_array($input['reason_for_submission'])) {
                $data['reason_for_submission'] = json_encode($input['reason_for_submission']);
            } elseif (isset($input['reason_for_submission'])) {
                $data['reason_for_submission'] = $input['reason_for_submission'];
            }
            
            if (isset($input['submission_results']) && is_array($input['submission_results'])) {
                $data['submission_results'] = json_encode($input['submission_results']);
            } elseif (isset($input['submission_results'])) {
                $data['submission_results'] = $input['submission_results'];
            }

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
            // Prepare data - convert checkbox arrays to JSON
            $data = [];
            
            // Get all input
            $input = $request->all();
            
            // List of fields to include
            $fillableFields = [
                'part_name', 'part_number', 'safety_government_regulation',
                'engineering_drawing_change_level', 'engineering_drawing_change_date',
                'additional_engineering_changes', 'additional_engineering_changes_date',
                'shown_on_drawing_no', 'purchase_order_no', 'weight_kg', 'checking_aid_no',
                'process_engineering_change_level', 'process_engineering_change_date',
                'supplier_name', 'supplier_code', 'street_address', 'city', 'state', 'zip',
                'customer_name_division', 'buyer_buyer_code', 'application',
                'contains_prohibited_substances', 'plastic_has_iso_marking',
                'reason_other_text', 'meets_all_requirements', 'explanation_if_not_meet',
                'explanation_comments', 'print_name', 'title', 'phone_no', 'fax_no',
                'signature_date',
            ];
            
            // Copy regular fields
            foreach ($fillableFields as $field) {
                if (isset($input[$field])) {
                    $data[$field] = $input[$field];
                }
            }
            
            // Handle signature file upload or path
            if ($request->hasFile('signature')) {
                // Delete old signature if exists
                if ($psw->supplier_authorized_signature && Storage::exists($psw->supplier_authorized_signature)) {
                    Storage::delete($psw->supplier_authorized_signature);
                }
                // Store new signature file
                $path = $request->file('signature')->store('psw/signatures', 'private');
                $data['supplier_authorized_signature'] = $path;
            } elseif (isset($input['supplier_authorized_signature']) && !empty($input['supplier_authorized_signature'])) {
                // If signature path is provided as string (not a file upload), keep it as is
                // This handles cases where signature is already stored
                $data['supplier_authorized_signature'] = $input['supplier_authorized_signature'];
            }
            
            // Handle JSON fields - convert arrays to JSON strings
            if (isset($input['submission_information']) && is_array($input['submission_information'])) {
                $data['submission_information'] = json_encode($input['submission_information']);
            } elseif (isset($input['submission_information'])) {
                $data['submission_information'] = $input['submission_information'];
            }
            
            if (isset($input['reason_for_submission']) && is_array($input['reason_for_submission'])) {
                $data['reason_for_submission'] = json_encode($input['reason_for_submission']);
            } elseif (isset($input['reason_for_submission'])) {
                $data['reason_for_submission'] = $input['reason_for_submission'];
            }
            
            if (isset($input['submission_results']) && is_array($input['submission_results'])) {
                $data['submission_results'] = json_encode($input['submission_results']);
            } elseif (isset($input['submission_results'])) {
                $data['submission_results'] = $input['submission_results'];
            }

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
     * Admin preview PSW form
     */
    public function adminPreview($pswId)
    {
        $psw = PartSubmissionWarrant::find($pswId);

        if (!$psw) {
            abort(404, 'PSW tidak ditemukan');
        }

        // Load related data
        $psw->load(['supplier', 'requestDocument.documentRequest.supplier']);

        return view('admin.forms.psw-preview', [
            'psw' => $psw,
        ]);
    }

    /**
     * Admin view PSW as PDF
     */
    public function adminViewPdf($pswId)
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
