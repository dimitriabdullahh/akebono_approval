<?php

namespace App\Http\Controllers;

use App\Events\DocumentSubmitted;
use App\Models\PartSubmissionWarrant;
use App\Models\RequestDocument;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class PartSubmissionWarrantController extends Controller
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Show the form for creating/editing a Part Submission Warrant
     */
    public function showForm($token)
    {
        $requestDocument = RequestDocument::where('id', $token)
            ->with(['documentRequest', 'documentRequest.supplier', 'requestPart'])
            ->first();

        if (!$requestDocument) {
            abort(404, 'Document request tidak ditemukan');
        }

        // Get or create PSW record
        $psw = PartSubmissionWarrant::where('request_document_id', $requestDocument->id)->first();

        if (!$psw) {
            // Create new PSW record if not exists
            $supplier = $requestDocument->documentRequest->supplier;
            
            // Parse address
            // Format: "Street, City, State ZipCode"
            // Example: "Jl. Industri No. 45, Karawang, Jawa Barat 41315"
            $streetAddress = '';
            $city = '';
            $state = '';
            $zip = '';
            
            if ($supplier->address) {
                // Split by comma to get parts
                $parts = array_map('trim', explode(',', $supplier->address));
                
                if (count($parts) >= 3) {
                    // Has street, city, and state+zip
                    $streetAddress = $parts[0];
                    $city = $parts[1];
                    
                    // State + Zip part: "Jawa Barat 41315"
                    $stateZipPart = $parts[2];
                    
                    // Try to extract state & zip (last numeric part is zip)
                    if (preg_match('/^(.+?)\s+(\d+)$/', $stateZipPart, $matches)) {
                        $state = $matches[1];
                        $zip = $matches[2];
                    } else {
                        $state = $stateZipPart;
                    }
                } else if (count($parts) === 2) {
                    // Has street and city, city might contain state+zip
                    $streetAddress = $parts[0];
                    $cityStateZipPart = $parts[1];
                    
                    // Try to extract city, state, zip from this part
                    // This is tricky since city might have multiple words
                    // For now, assume it's "City State Zip"
                    if (preg_match('/^(.+?)\s+(.+?)\s+(\d+)$/', $cityStateZipPart, $matches)) {
                        $city = $matches[1];
                        $state = $matches[2];
                        $zip = $matches[3];
                    } else {
                        $city = $cityStateZipPart;
                    }
                } else if (count($parts) === 1) {
                    $streetAddress = $parts[0];
                }
            }
            
            $psw = new PartSubmissionWarrant([
                'request_document_id' => $requestDocument->id,
                'supplier_id' => $requestDocument->documentRequest->supplier_id,
                'status' => 'DRAFT',
                'part_number' => $requestDocument->requestPart->part_number ?? '',
                'part_name' => $requestDocument->requestPart->part_name ?? '',
                'supplier_name' => $supplier->name ?? '',
                'supplier_code' => $supplier->vendor_code ?? '',
                'street_address' => $streetAddress,
                'city' => $city,
                'state' => $state,
                'zip' => $zip,
                'customer_name_division' => 'PT. AAIJ / Purchasing / QA',
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
        $requestDocument = RequestDocument::where('id', $token)
            ->with(['requestPart', 'documentRequest.supplier'])
            ->first();

        if (!$requestDocument) {
            return response()->json(['error' => 'Document not found'], 404);
        }

        $psw = PartSubmissionWarrant::where('request_document_id', $requestDocument->id)
            ->first();

        if (!$psw) {
            return response()->json(['error' => 'PSW not found'], 404);
        }

        // Return flat data structure expected by form JavaScript
        $data = $psw->toArray();
        
        // Ensure dates are formatted properly
        if ($psw->engineering_drawing_change_date) {
            $data['engineering_drawing_change_date'] = $psw->engineering_drawing_change_date->format('Y-m-d');
        }
        if ($psw->additional_engineering_changes_date) {
            $data['additional_engineering_changes_date'] = $psw->additional_engineering_changes_date->format('Y-m-d');
        }
        if ($psw->process_engineering_change_date) {
            $data['process_engineering_change_date'] = $psw->process_engineering_change_date->format('Y-m-d');
        }
        if ($psw->signature_date) {
            $data['signature_date'] = $psw->signature_date->format('Y-m-d');
        }

        // Fallback to requestPart data if database fields are empty
        if (empty($data['part_number']) && $requestDocument->requestPart) {
            $data['part_number'] = $requestDocument->requestPart->part_number;
        }
        if (empty($data['part_name']) && $requestDocument->requestPart) {
            $data['part_name'] = $requestDocument->requestPart->part_name;
        }

        // Populate supplier data if available and database fields are empty
        if ($requestDocument->documentRequest && $requestDocument->documentRequest->supplier) {
            $supplier = $requestDocument->documentRequest->supplier;
            
            if (empty($data['supplier_name'])) {
                $data['supplier_name'] = $supplier->name;
            }
            if (empty($data['supplier_code'])) {
                $data['supplier_code'] = $supplier->vendor_code;
            }
            
            // Parse address if available
            // Format: "Street, City, State ZipCode"
            // Example: "Jl. Industri No. 45, Karawang, Jawa Barat 41315"
            if (!empty($supplier->address)) {
                // Split by comma to get parts
                $parts = array_map('trim', explode(',', $supplier->address));
                
                if (count($parts) >= 3) {
                    // Has street, city, and state+zip
                    if (empty($data['street_address'])) {
                        $data['street_address'] = $parts[0];
                    }
                    if (empty($data['city'])) {
                        $data['city'] = $parts[1];
                    }
                    
                    // State + Zip part: "Jawa Barat 41315"
                    $stateZipPart = $parts[2];
                    
                    // Try to extract state & zip (last numeric part is zip)
                    if (preg_match('/^(.+?)\s+(\d+)$/', $stateZipPart, $matches)) {
                        if (empty($data['state'])) {
                            $data['state'] = $matches[1];
                        }
                        if (empty($data['zip'])) {
                            $data['zip'] = $matches[2];
                        }
                    } else {
                        if (empty($data['state'])) {
                            $data['state'] = $stateZipPart;
                        }
                    }
                } else if (count($parts) === 2) {
                    // Has street and city, city might contain state+zip
                    if (empty($data['street_address'])) {
                        $data['street_address'] = $parts[0];
                    }
                    
                    $cityStateZipPart = $parts[1];
                    
                    // Try to extract city, state, zip from this part
                    if (preg_match('/^(.+?)\s+(.+?)\s+(\d+)$/', $cityStateZipPart, $matches)) {
                        if (empty($data['city'])) {
                            $data['city'] = $matches[1];
                        }
                        if (empty($data['state'])) {
                            $data['state'] = $matches[2];
                        }
                        if (empty($data['zip'])) {
                            $data['zip'] = $matches[3];
                        }
                    } else {
                        if (empty($data['city'])) {
                            $data['city'] = $cityStateZipPart;
                        }
                    }
                } else if (count($parts) === 1) {
                    if (empty($data['street_address'])) {
                        $data['street_address'] = $parts[0];
                    }
                }
            }
        }

        return response()->json($data);
    }

    /**
     * Save draft (auto-save)
     */
    public function saveDraft($pswId, Request $request)
    {
        $psw = PartSubmissionWarrant::with('requestDocument')->find($pswId);

        if (!$psw) {
            return response()->json(['error' => 'PSW not found'], 404);
        }

        // Validasi: pastikan psw ini benar-benar milik request_document yang valid
        if (!$psw->requestDocument) {
            return response()->json(['error' => 'Invalid request document association'], 400);
        }

        try {
            // Validate input data (more lenient for draft - only check data types)
            // Clean empty string dates before validation
            $input = $request->all();
            $dateFields = [
                'engineering_drawing_change_date',
                'additional_engineering_changes_date', 
                'process_engineering_change_date',
                'signature_date'
            ];
            
            foreach ($dateFields as $field) {
                if (isset($input[$field]) && $input[$field] === '') {
                    $input[$field] = null;
                }
            }
            
            $validated = validator($input, [
                'part_name' => 'nullable|string|max:255',
                'part_number' => 'nullable|string|max:255',
                'safety_government_regulation' => 'nullable|boolean',
                'engineering_drawing_change_level' => 'nullable|string|max:255',
                'engineering_drawing_change_date' => 'nullable|date',
                'additional_engineering_changes' => 'nullable|string|max:255',
                'additional_engineering_changes_date' => 'nullable|date',
                'shown_on_drawing_no' => 'nullable|string|max:255',
                'purchase_order_no' => 'nullable|string|max:255',
                'weight_kg' => 'nullable|numeric',
                'checking_aid_no' => 'nullable|string|max:255',
                'process_engineering_change_level' => 'nullable|string|max:255',
                'process_engineering_change_date' => 'nullable|date',
                'supplier_name' => 'nullable|string|max:255',
                'supplier_code' => 'nullable|string|max:255',
                'street_address' => 'nullable|string|max:500',
                'city' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'zip' => 'nullable|string|max:20',
                'customer_name_division' => 'nullable|string|max:255',
                'buyer_buyer_code' => 'nullable|string|max:255',
                'application' => 'nullable|string|max:255',
                'submission_information' => 'nullable|array',
                'contains_prohibited_substances' => 'nullable|boolean',
                'plastic_has_iso_marking' => 'nullable|boolean',
                'reason_for_submission' => 'nullable|array',
                'submission_results' => 'nullable|array',
                'meets_all_requirements' => 'nullable|boolean',
                'explanation_if_not_meet' => 'nullable|string|max:1000',
                'explanation_comments' => 'nullable|string|max:1000',
                'print_name' => 'nullable|string|max:255',
                'title' => 'nullable|string|max:255',
                'phone_no' => 'nullable|string|max:50',
                'fax_no' => 'nullable|string|max:50',
                'signature_date' => 'nullable|date',
            ])->validate();

            // Update status menjadi DRAFT karena user sudah mulai mengisi
            $validated['status'] = 'DRAFT';

            // Update PSW record - Laravel will auto-cast arrays to JSON
            $psw->update($validated);

            // Store JSON representation for reference
            $psw->update(['form_data_json' => $psw->getFormDataArray()]);

            // TIDAK perlu update status RequestDocument karena:
            // - RequestDocument.status digunakan untuk tracking approval workflow
            // - PartSubmissionWarrant.status digunakan untuk tracking pengisian form
            // - Keduanya independen

            return response()->json([
                'success' => true,
                'message' => 'Draft saved successfully',
                'psw' => $psw,
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
                'message' => 'Error saving draft: ' . $e->getMessage(),
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
            // Clean empty string dates before validation
            $input = $request->all();
            $dateFields = [
                'engineering_drawing_change_date',
                'additional_engineering_changes_date', 
                'process_engineering_change_date',
                'signature_date'
            ];
            
            foreach ($dateFields as $field) {
                if (isset($input[$field]) && $input[$field] === '') {
                    $input[$field] = null;
                }
            }
            
            // Validate input data
            $validated = validator($input, [
                'part_name' => 'required|string|max:255',
                'part_number' => 'required|string|max:255',
                'safety_government_regulation' => 'required|boolean',
                'engineering_drawing_change_level' => 'nullable|string|max:255',
                'engineering_drawing_change_date' => 'nullable|date',
                'additional_engineering_changes' => 'nullable|string|max:255',
                'additional_engineering_changes_date' => 'nullable|date',
                'shown_on_drawing_no' => 'nullable|string|max:255',
                'purchase_order_no' => 'nullable|string|max:255',
                'weight_kg' => 'nullable|numeric',
                'checking_aid_no' => 'nullable|string|max:255',
                'process_engineering_change_level' => 'nullable|string|max:255',
                'process_engineering_change_date' => 'nullable|date',
                'supplier_name' => 'required|string|max:255',
                'supplier_code' => 'required|string|max:255',
                'street_address' => 'nullable|string|max:500',
                'city' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'zip' => 'nullable|string|max:20',
                'customer_name_division' => 'nullable|string|max:255',
                'buyer_buyer_code' => 'nullable|string|max:255',
                'application' => 'nullable|string|max:255',
                'submission_information' => 'nullable|array',
                'contains_prohibited_substances' => 'nullable|boolean',
                'plastic_has_iso_marking' => 'nullable|boolean',
                'reason_for_submission' => 'nullable|array',
                'submission_results' => 'nullable|array',
                'meets_all_requirements' => 'required|boolean',
                'explanation_if_not_meet' => 'nullable|string|max:1000',
                'explanation_comments' => 'nullable|string|max:1000',
                'print_name' => 'required|string|max:255',
                'title' => 'required|string|max:255',
                'phone_no' => 'nullable|string|max:50',
                'fax_no' => 'nullable|string|max:50',
                'signature_date' => 'required|date',
            ])->validate();

            \Log::info('[PSW Submit] Validation passed', ['psw_id' => $pswId]);

            // Handle array fields - keep as arrays, Laravel will auto-cast to JSON
            // No need to manually json_encode
            
            // Update PSW record with latest form data
            $psw->update($validated);
            
            \Log::info('[PSW Submit] PSW record updated successfully');

            // Validate mandatory fields after data is saved
            $errors = $psw->validateForSubmission();

            if (!empty($errors)) {
                \Log::warning('[PSW Submit] Business validation failed', ['errors' => $errors]);
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(', ', $errors),
                    'errors' => $errors,
                ], 422);
            }

            \Log::info('[PSW Submit] Business validation passed, getting form data array...');

            // Get form data array (this might throw error if there's an issue)
            try {
                $formDataArray = $psw->getFormDataArray();
                \Log::info('[PSW Submit] Form data array obtained successfully');
            } catch (\Exception $e) {
                \Log::error('[PSW Submit] Error getting form data array', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }

            \Log::info('[PSW Submit] Updating status to SUBMITTED...');
            
            // Generate QR Code Signature if all fields present
            $qrCodeData = null;
            try {
                if (!empty($psw->print_name) && !empty($psw->title) && !empty($psw->phone_no) && !empty($psw->fax_no)) {
                    $qrCodeData = \App\Services\QrCodeService::generatePswSignature([
                        'print_name' => $psw->print_name,
                        'title' => $psw->title,
                        'phone_no' => $psw->phone_no,
                        'fax_no' => $psw->fax_no,
                    ]);
                    
                    if ($qrCodeData) {
                        \Log::info('[PSW Submit] QR code signature generated successfully');
                    } else {
                        \Log::warning('[PSW Submit] Failed to generate QR code signature - will proceed without QR');
                    }
                }
            } catch (\Throwable $e) {
                \Log::error('[PSW Submit] Exception during QR code generation - proceeding without QR', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
                // Continue without QR - not critical for submission
                $qrCodeData = null;
            }

            // Update status to SUBMITTED
            $psw->update([
                'status' => 'SUBMITTED',
                'submitted_at' => now(),
                'form_data_json' => $formDataArray,
                'signature_qr_data' => $qrCodeData, // Save QR code (or null if failed)
            ]);

            // Update request document status
            $requestDocument = RequestDocument::find($psw->request_document_id);
            if ($requestDocument) {
                // Check if this is a revision resubmission
                $isRevision = $requestDocument->status === 'REVISE';
                
                try {
                    if ($isRevision) {
                        // Use the resubmit method to handle revision logic
                        // For PSW, we pass 'form' as the path since it's a form-based document
                        $requestDocument->resubmit('form');
                    } else {
                        // Normal first-time submission
                        $requestDocument->update([
                            'status' => 'SUBMITTED',
                            'file_path' => 'form',
                            'pdf_path' => 'form', // Consistency with other documents
                        ]);
                    }
                    
                    $requestDocument->refresh(); // Force reload to ensure data is fresh
                    
                    // Dispatch DocumentSubmitted event to trigger approval blast email
                    try {
                        \Log::info('[PSW Submit] About to dispatch DocumentSubmitted event');
                        DocumentSubmitted::dispatch($requestDocument, 'PSW');
                        \Log::info('[PSW Submit] DocumentSubmitted event dispatched successfully');
                    } catch (\Throwable $e) {
                        \Log::error('[PSW Submit] Failed to dispatch DocumentSubmitted event', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                            'line' => $e->getLine(),
                            'file' => $e->getFile()
                        ]);
                        // Don't re-throw - email failure shouldn't block submission
                    }
                } catch (\Exception $e) {
                    \Log::error('[PSW Submit] Error updating request document', [
                        'request_document_id' => $requestDocument->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e; // Re-throw to be caught by outer catch
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Form submitted successfully',
                'redirect' => route('supplier.dashboard'),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('[PSW Submit] Validation exception', [
                'psw_id' => $pswId,
                'errors' => $e->errors(),
            ]);
            
            $errorDetails = [];
            foreach ($e->errors() as $field => $messages) {
                $errorDetails[$field] = $messages[0] ?? 'Validation failed';
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', array_values($errorDetails)),
                'errors' => $errorDetails,
            ], 422);
        } catch (\Exception $e) {
            \Log::error('[PSW Submit] Fatal error during submission', [
                'psw_id' => $pswId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error submitting form: ' . $e->getMessage(),
            ], 500);
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

}
