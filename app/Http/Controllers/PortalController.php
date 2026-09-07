<?php

namespace App\Http\Controllers;

use App\Models\DocumentRequest;
use App\Models\RequestDocument;
use App\Services\DocumentUploadService;
use App\Services\NotificationService;
use App\Services\ReviewApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PortalController extends Controller
{
    private DocumentUploadService $uploadService;
    private NotificationService $notificationService;
    private ReviewApprovalService $reviewService;

    public function __construct(
        DocumentUploadService $uploadService,
        NotificationService $notificationService,
        ReviewApprovalService $reviewService
    ) {
        $this->uploadService = $uploadService;
        $this->notificationService = $notificationService;
        $this->reviewService = $reviewService;
    }

    /**
     * Access portal via token (auto-login supplier and redirect to their portal)
     */
    public function accessViaToken(string $token)
    {
        $documentRequest = DocumentRequest::where('access_token', $token)
            ->with(['supplier', 'requestDocuments.documentType'])
            ->first();

        if (!$documentRequest) {
            return redirect()->route('login')
                ->with('error', 'Link tidak valid atau sudah kadaluarsa. Silakan login dengan email Anda.');
        }

        // Get supplier user account
        $supplier = $documentRequest->supplier;
        $user = \App\Models\User::where('supplier_id', $supplier->id)->first();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Akun supplier tidak ditemukan. Silakan hubungi admin.');
        }

        // Auto-login the supplier user
        auth()->login($user);

        // Redirect to supplier dashboard with success message
        return redirect()->route('supplier.dashboard')
            ->with('success', 'Selamat datang! Anda dapat langsung mengupload dokumen untuk request: ' . $documentRequest->model);
    }

    /**
     * Tampilkan portal upload dokumen supplier (via token)
     */
    public function show(string $token)
    {
        $request = DocumentRequest::where('access_token', $token)
            ->with([
                'supplier', 
                'requestParts.requestDocuments.documentType',
                'requestParts.requestDocuments.histories',
                'requestDocuments.documentType', 
                'requestDocuments.histories'
            ])
            ->first();

        if (!$request) {
            abort(404, 'Request tidak ditemukan atau token invalid');
        }

        // Calculate total progress from ALL parts
        $totalMandatory = 0;
        $totalSubmitted = 0;
        
        // Hitung dari semua parts (untuk multi-part request)
        if ($request->hasMultipleParts()) {
            foreach ($request->requestParts as $part) {
                $partMandatory = $part->requestDocuments()
                    ->whereHas('documentType', function ($q) {
                        $q->where('is_mandatory', true);
                    })
                    ->count();
                    
                $partSubmitted = $part->requestDocuments()
                    ->whereHas('documentType', function ($q) {
                        $q->where('is_mandatory', true);
                    })
                    ->whereNotNull('file_path')
                    ->whereIn('status', ['SUBMITTED', 'APPROVED', 'REVISE'])
                    ->count();
                    
                $totalMandatory += $partMandatory;
                $totalSubmitted += $partSubmitted;
            }
        } else {
            // Fallback untuk backward compatibility (request tanpa parts)
            $totalMandatory = $request->requestDocuments()
                ->whereHas('documentType', function ($q) {
                    $q->where('is_mandatory', true);
                })
                ->count();

            $totalSubmitted = $request->requestDocuments()
                ->whereHas('documentType', function ($q) {
                    $q->where('is_mandatory', true);
                })
                ->whereNotNull('file_path')
                ->whereIn('status', ['SUBMITTED', 'APPROVED', 'REVISE'])
                ->count();
        }

        $progress = $totalMandatory > 0 ? (int) (($totalSubmitted / $totalMandatory) * 100) : 0;

        // Group documents by status (for non-part requests)
        $documentRequests = $request->requestDocuments()
            ->with(['documentType', 'histories'])
            ->get()
            ->groupBy(function ($doc) {
                return $doc->status === 'REVISE' ? 'revise' : 'other';
            });

        return view('portal.upload', compact('request', 'documentRequests', 'progress', 'token', 'totalMandatory', 'totalSubmitted'));
    }

    /**
     * Upload dokumen supplier
     */
    public function upload(Request $request, string $token)
    {
        $documentRequest = DocumentRequest::where('access_token', $token)->first();
        if (!$documentRequest) {
            abort(404);
        }

        $validated = $request->validate([
            'documents' => 'required|array',
            'documents.*' => 'required|file',
        ]);

        try {
            $uploadedCount = 0;

            foreach ($request->file('documents', []) as $documentId => $file) {
                $requestDoc = RequestDocument::find($documentId);

                if (!$requestDoc) {
                    continue;
                }
                
                // Validasi bahwa document ini memang milik request ini
                // Baik melalui document_request_id langsung atau melalui request_part
                $belongsToRequest = false;
                
                if ($requestDoc->document_request_id === $documentRequest->id) {
                    $belongsToRequest = true;
                } elseif ($requestDoc->requestPart && $requestDoc->requestPart->document_request_id === $documentRequest->id) {
                    $belongsToRequest = true;
                }
                
                if (!$belongsToRequest) {
                    continue;
                }

                // Skip jika sudah APPROVED
                if ($requestDoc->status === 'APPROVED') {
                    continue;
                }

                $this->uploadService->uploadDocument($requestDoc, $file, $token);
                $uploadedCount++;
            }

            // Check jika semua mandatory docs sudah submitted SETELAH upload
            // Hanya kirim notifikasi jika:
            // 1. Belum pernah dikirim sebelumnya (documents_submitted_notified_at === null)
            // 2. Sekarang semua mandatory docs sudah submitted
            $documentRequest->refresh();
            
            if (
                is_null($documentRequest->documents_submitted_notified_at) &&
                $this->uploadService->allMandatoryDocumentsSubmitted($documentRequest)
            ) {
                $this->notificationService->notifyDocumentsSubmitted($documentRequest);
                
                // Mark bahwa notifikasi sudah dikirim
                $documentRequest->update([
                    'documents_submitted_notified_at' => now()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "{$uploadedCount} dokumen berhasil diunggah",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Re-upload dokumen (untuk revisi)
     */
    public function reupload(Request $request, string $token, int $documentId)
    {
        $documentRequest = DocumentRequest::where('access_token', $token)->first();
        if (!$documentRequest) {
            abort(404);
        }

        $requestDoc = RequestDocument::find($documentId);
        if (!$requestDoc) {
            abort(404);
        }
        
        // Validasi bahwa document ini memang milik request ini
        $belongsToRequest = false;
        
        if ($requestDoc->document_request_id === $documentRequest->id) {
            $belongsToRequest = true;
        } elseif ($requestDoc->requestPart && $requestDoc->requestPart->document_request_id === $documentRequest->id) {
            $belongsToRequest = true;
        }
        
        if (!$belongsToRequest) {
            abort(404);
        }

        $validated = $request->validate([
            'file' => 'required|file',
        ]);

        try {
            $this->uploadService->reuploadDocument($requestDoc, $request->file('file'), $token);

            // Check jika semua mandatory docs sudah submitted SETELAH reupload
            // Hanya kirim notifikasi jika:
            // 1. Belum pernah dikirim sebelumnya (documents_submitted_notified_at === null)
            // 2. Sekarang semua mandatory docs sudah submitted
            $documentRequest->refresh();
            
            if (
                is_null($documentRequest->documents_submitted_notified_at) &&
                $this->uploadService->allMandatoryDocumentsSubmitted($documentRequest)
            ) {
                $this->notificationService->notifyDocumentsSubmitted($documentRequest);
                
                // Mark bahwa notifikasi sudah dikirim
                $documentRequest->update([
                    'documents_submitted_notified_at' => now()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Dokumen revisi berhasil diunggah',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Download dokumen yang sudah approved
     */
    public function download(string $token, int $documentId)
    {
        $documentRequest = DocumentRequest::where('access_token', $token)->first();
        if (!$documentRequest) {
            abort(404);
        }

        $requestDoc = RequestDocument::find($documentId);
        if (!$requestDoc) {
            abort(404);
        }
        
        // Validasi bahwa document ini memang milik request ini
        $belongsToRequest = false;
        
        if ($requestDoc->document_request_id === $documentRequest->id) {
            $belongsToRequest = true;
        } elseif ($requestDoc->requestPart && $requestDoc->requestPart->document_request_id === $documentRequest->id) {
            $belongsToRequest = true;
        }
        
        if (!$belongsToRequest) {
            abort(404);
        }

        try {
            $filePath = $this->uploadService->downloadDocument($requestDoc);

            return Storage::disk('local')->download($filePath, $requestDoc->original_filename);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Preview dokumen
     */
    public function preview(string $token, int $documentId)
    {
        $documentRequest = DocumentRequest::where('access_token', $token)->first();
        if (!$documentRequest) {
            abort(404);
        }

        $requestDoc = RequestDocument::find($documentId);
        if (!$requestDoc) {
            abort(404);
        }
        
        // Validasi bahwa document ini memang milik request ini
        $belongsToRequest = false;
        
        if ($requestDoc->document_request_id === $documentRequest->id) {
            $belongsToRequest = true;
        } elseif ($requestDoc->requestPart && $requestDoc->requestPart->document_request_id === $documentRequest->id) {
            $belongsToRequest = true;
        }
        
        if (!$belongsToRequest) {
            abort(404);
        }

        if (!$requestDoc->file_path) {
            abort(404);
        }

        return Storage::disk('local')->response($requestDoc->file_path);
    }

    /**
     * Tampilkan halaman notifikasi dan pesan
     */
    public function notifications(string $token)
    {
        $request = DocumentRequest::where('access_token', $token)
            ->with(['requestDocuments.documentType', 'notificationLogs'])
            ->first();

        if (!$request) {
            abort(404, 'Request tidak ditemukan atau token invalid');
        }

        $alerts = [];
        $notificationLogs = $request->notificationLogs()
            ->orderBy('sent_at', 'desc')
            ->paginate(10);

        return view('portal.notifications', compact('request', 'alerts', 'notificationLogs', 'token'));
    }

    /**
     * Save Draft untuk multi-part request
     * Menyimpan status last_saved_at untuk setiap part
     */
    public function saveDraft(Request $request, string $token)
    {
        $documentRequest = DocumentRequest::where('access_token', $token)->first();
        if (!$documentRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Request tidak ditemukan',
            ], 404);
        }

        try {
            $savedPartsCount = 0;
            
            // Update last_saved_at untuk semua parts yang memiliki upload
            if ($documentRequest->hasMultipleParts()) {
                foreach ($documentRequest->requestParts as $part) {
                    // Check apakah part ini memiliki dokumen yang sudah diupload
                    $hasUploads = $part->requestDocuments()
                        ->whereNotNull('file_path')
                        ->exists();
                    
                    if ($hasUploads) {
                        $part->update([
                            'last_saved_at' => now(),
                            'submission_status' => 'DRAFT', // Pastikan status tetap DRAFT
                        ]);
                        $savedPartsCount++;
                    }
                }
            } else {
                // Backward compatibility untuk request tanpa parts
                // Just mark that draft was saved
                $savedPartsCount = 1;
            }

            return response()->json([
                'success' => true,
                'message' => "Draft berhasil disimpan untuk {$savedPartsCount} part(s)",
                'saved_at' => now()->format('d M Y H:i:s'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan draft: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if document type is a form-based document (PSW, PS, IS)
     */
    private function isFormDocument($documentType): bool
    {
        return in_array($documentType->code, ['PSW', 'PACKAGING_STANDARD', 'INSPECTION_STANDARD']);
    }

    /**
     * Get form route for document type
     */
    private function getFormRoute($requestDocumentId, $documentTypeCode): ?string
    {
        switch ($documentTypeCode) {
            case 'PSW':
                return route('psw.form', ['token' => $requestDocumentId]);
            case 'PACKAGING_STANDARD':
                return route('ps.form', ['token' => $requestDocumentId]);
            case 'INSPECTION_STANDARD':
                return route('inspection-standard.form', ['token' => $requestDocumentId]);
            default:
                return null;
        }
    }

    /**
     * Submit All Documents untuk multi-part request
     * Validasi bahwa semua mandatory docs dari semua parts sudah lengkap
     */
    public function submitAll(Request $request, string $token)
    {
        $documentRequest = DocumentRequest::where('access_token', $token)->first();
        if (!$documentRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Request tidak ditemukan',
            ], 404);
        }

        try {
            // Check apakah semua mandatory documents dari semua parts sudah lengkap
            $missingDocs = $this->uploadService->getMissingMandatoryDocuments($documentRequest);
            
            if (!empty($missingDocs)) {
                // Ada dokumen mandatory yang belum lengkap
                $message = "Beberapa dokumen mandatory masih belum lengkap:\n\n";
                
                foreach ($missingDocs as $missing) {
                    $message .= "Part: {$missing['part_name']} ({$missing['part_number']})\n";
                    $message .= "Missing:\n";
                    foreach ($missing['documents'] as $doc) {
                        $message .= "  • {$doc}\n";
                    }
                    $message .= "\n";
                }
                
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'missing_docs' => $missingDocs,
                ], 422);
            }

            // Semua mandatory docs lengkap, update status semua parts menjadi SUBMITTED
            $submittedPartsCount = 0;
            
            if ($documentRequest->hasMultipleParts()) {
                foreach ($documentRequest->requestParts as $part) {
                    // Only submit if not already submitted
                    if ($part->submission_status !== 'SUBMITTED') {
                        $part->update([
                            'submission_status' => 'SUBMITTED',
                            'submitted_at' => now(),
                            'submitted_by' => auth()->id(), // Supplier user ID if authenticated, null if via portal
                        ]);
                        $submittedPartsCount++;
                    }
                }
            }

            // Send notification jika belum pernah dikirim
            if (is_null($documentRequest->documents_submitted_notified_at)) {
                $this->notificationService->notifyDocumentsSubmitted($documentRequest);
                
                $documentRequest->update([
                    'documents_submitted_notified_at' => now()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Semua dokumen berhasil disubmit! Total {$submittedPartsCount} part(s) telah disubmit.",
                'submitted_at' => now()->format('d M Y H:i:s'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal submit dokumen: ' . $e->getMessage(),
            ], 500);
        }
    }
}
