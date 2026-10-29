<?php

namespace App\Http\Controllers;

use App\Models\DocumentRequest;
use App\Models\RequestDocument;
use App\Models\DocumentType;
use App\Jobs\DocumentsSubmittedToAkebonoJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use ZipArchive;

class SupplierController extends BaseSupplierController
{
    public function __construct()
    {
        $this->middleware(['auth', 'supplier']);
    }

    /**
     * Display dashboard for supplier
     */
    public function dashboard()
    {
        $supplier = $this->getAuthenticatedSupplier();
        $user = $this->getAuthenticatedUser();

        // Get all document requests for this supplier
        $documentRequests = DocumentRequest::where('supplier_id', $supplier->id)
            ->with(['requestDocuments.documentType', 'requestParts'])
            ->orderBy('due_date', 'desc')
            ->get();

        // Get statistics
        $totalRequests = $documentRequests->count();
        $pendingRequests = $documentRequests->filter(function($request) {
            return $request->requestDocuments->where('status', 'PENDING')->count() > 0;
        })->count();
        
        $approvedRequests = $documentRequests->filter(function($request) {
            return $request->requestDocuments->where('status', '!=', 'APPROVED')->count() === 0
                && $request->requestDocuments->count() > 0;
        })->count();

        // TEMPORARILY DISABLED: Outstanding documents query
        // Get outstanding documents (PENDING or REJECTED)
        // $outstandingDocuments = RequestDocument::whereHas('documentRequest', function($query) use ($supplier) {
        //         $query->where('supplier_id', $supplier->id);
        //     })
        //     ->whereIn('status', ['PENDING', 'REJECTED'])
        //     ->with(['documentType', 'documentRequest.requestParts'])
        //     ->orderByRaw("CASE WHEN status = 'REJECTED' THEN 0 ELSE 1 END")
        //     ->orderBy('created_at', 'asc')
        //     ->get();
        
        // Provide empty collection to prevent errors in view
        $outstandingDocuments = collect();

        return view('supplier.dashboard', compact(
            'supplier',
            'documentRequests',
            'totalRequests',
            'pendingRequests',
            'approvedRequests',
            'outstandingDocuments'
        ));
    }

    /**
     * Display specific document request detail
     */
    public function showRequest($requestId)
    {
        $supplier = $this->getAuthenticatedSupplier();

        $documentRequest = DocumentRequest::where('id', $requestId)
            ->where('supplier_id', $supplier->id)
            ->with([
                'requestParts.requestDocuments.documentType',
                'requestParts.requestDocuments.inspectionStandard',
                'requestParts.requestDocuments.packagingStandard',
                'requestParts.requestDocuments.partSubmissionWarrant',
                'requestParts',
                'supplier',
                'creator'
            ])
            ->firstOrFail();

        // Group documents by status for first part (default selection)
        $firstPart = $documentRequest->requestParts->first();
        $documents = $firstPart ? $firstPart->requestDocuments->groupBy('status') : collect();

        // Calculate progress for first part
        if ($firstPart) {
            $totalDocs = $firstPart->requestDocuments->count();
            $approvedDocs = $firstPart->requestDocuments->where('status', 'APPROVED')->count();
            $progress = $totalDocs > 0 ? round(($approvedDocs / $totalDocs) * 100) : 0;
        } else {
            $progress = 0;
        }

        return view('supplier.request-detail', compact(
            'documentRequest',
            'documents',
            'progress',
            'firstPart'
        ));
    }

    /**
     * Get documents for a specific part (AJAX)
     */
    public function getPartDocuments($requestId, $partId)
    {
        $supplier = $this->getAuthenticatedSupplier();

        // Verify part belongs to request and supplier
        $part = \App\Models\RequestPart::where('id', $partId)
            ->where('document_request_id', $requestId)
            ->whereHas('documentRequest', function($query) use ($supplier) {
                $query->where('supplier_id', $supplier->id);
            })
            ->with([
                'requestDocuments.documentType',
                'requestDocuments.inspectionStandard',
                'requestDocuments.packagingStandard',
                'requestDocuments.partSubmissionWarrant',
                'documentRequest'
            ])
            ->firstOrFail();

        // Calculate counters
        $totalDocs = $part->requestDocuments->count();
        $pendingCount = $part->requestDocuments->where('status', 'PENDING')->count();
        $reviseCount = $part->requestDocuments->where('status', 'REVISE')->count();
        $submittedCount = $part->requestDocuments->where('status', 'SUBMITTED')->count();
        $approvedCount = $part->requestDocuments->where('status', 'APPROVED')->count();
        $progress = $totalDocs > 0 ? round((($submittedCount + $approvedCount) / $totalDocs) * 100) : 0;

        return response()->json([
            'success' => true,
            'part' => [
                'id' => $part->id,
                'name' => $part->part_name,
                'number' => $part->part_number,
            ],
            'counters' => [
                'total' => $totalDocs,
                'pending' => $pendingCount,
                'revise' => $reviseCount,
                'submitted' => $submittedCount,
                'approved' => $approvedCount,
            ],
            'progress' => $progress,
            'html' => view('supplier.partials.documents-table', compact('part'))->render()
        ]);
    }

    /**
     * Display document detail
     */
    public function showDocument($documentId)
    {
        $supplier = $this->getAuthenticatedSupplier();

        $document = RequestDocument::whereHas('documentRequest', function($query) use ($supplier) {
                $query->where('supplier_id', $supplier->id);
            })
            ->with(['documentType', 'documentRequest', 'histories.actor'])
            ->findOrFail($documentId);

        return view('supplier.document-detail', compact('document'));
    }

    /**
     * Show upload form for a document
     */
    public function uploadForm($documentId)
    {
        $supplier = $this->getAuthenticatedSupplier();

        $document = RequestDocument::whereHas('documentRequest', function($query) use ($supplier) {
                $query->where('supplier_id', $supplier->id);
            })
            ->with(['documentType', 'documentRequest'])
            ->findOrFail($documentId);

        // Only allow upload if document is PENDING or REVISE
        if (!in_array($document->status, ['PENDING', 'REVISE'])) {
            return redirect()->back()->with('error', 'Dokumen ini tidak dapat diupload lagi.');
        }

        return view('supplier.upload', compact('document'));
    }

    /**
     * Handle document upload
     */
    public function uploadDocument(Request $request, $documentId)
    {
        $user = $this->getAuthenticatedUser();
        $supplier = $this->getAuthenticatedSupplier();

        $document = RequestDocument::whereHas('documentRequest', function($query) use ($supplier) {
                $query->where('supplier_id', $supplier->id);
            })
            ->with(['documentType', 'documentRequest'])
            ->findOrFail($documentId);

        // Validate upload permission
        if (!in_array($document->status, ['PENDING', 'REVISE'])) {
            return redirect()->back()->with('error', 'Dokumen ini tidak dapat diupload lagi.');
        }

        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240', // 10MB max
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $file = $request->file('file');
            $filename = time() . '_' . $document->documentType->name . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs(
                'documents/' . $document->documentRequest->id,
                $filename,
                'public'
            );

            // Update document
            $previousStatus = $document->status;
            $document->file_path = $path;
            $document->original_filename = $file->getClientOriginalName();
            $document->uploaded_at = now();
            $document->status = 'SUBMITTED';
            $document->notes = $request->input('notes');
            $document->save();

            // Create history record
            $document->histories()->create([
                'action' => $previousStatus === 'REVISE' ? 'RESUBMITTED' : 'UPLOADED',
                'actor_id' => $user->id, // User supplier yang upload
                'actor_name' => $user->name,
                'note' => $request->input('notes') ?? 'Dokumen diupload oleh supplier',
            ]);

            // Check if all documents submitted, send notification
            $allSubmitted = $document->documentRequest->requestDocuments()
                ->where('status', 'PENDING')
                ->count() === 0;

            if ($allSubmitted) {
                // Get all submitted documents with their details
                $submittedDocs = $document->documentRequest->requestDocuments()
                    ->where('status', 'SUBMITTED')
                    ->with('documentType')
                    ->get();
                
                $submittedDocumentsData = $submittedDocs->map(function($doc) {
                    return [
                        'code' => $doc->documentType->code ?? '',
                        'name' => $doc->documentType->name ?? ''
                    ];
                })->toArray();
                
                DocumentsSubmittedToAkebonoJob::dispatch(
                    $document->documentRequest,
                    $submittedDocs->count(),
                    $submittedDocumentsData
                );
            }

            DB::commit();

            return redirect()->route('supplier.request', $document->document_request_id)
                ->with('success', 'Dokumen berhasil diupload!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Document upload failed', [
                'document_id' => $documentId,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Gagal upload dokumen: ' . $e->getMessage());
        }
    }

    /**
     * View document (display inline in browser)
     */
    public function viewDocument($documentId)
    {
        $supplier = $this->getAuthenticatedSupplier();

        $document = RequestDocument::whereHas('documentRequest', function($query) use ($supplier) {
                $query->where('supplier_id', $supplier->id);
            })
            ->findOrFail($documentId);

        if (!$document->file_path || !Storage::disk('public')->exists($document->file_path)) {
            return redirect()->back()->with('error', 'File tidak ditemukan.');
        }

        $filePath = Storage::disk('public')->path($document->file_path);
        $mimeType = Storage::disk('public')->mimeType($document->file_path);

        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . ($document->original_filename ?? basename($document->file_path)) . '"'
        ]);
    }

    /**
     * Download a document (force download)
     */
    public function downloadDocument($documentId)
    {
        $supplier = $this->getAuthenticatedSupplier();

        $document = RequestDocument::whereHas('documentRequest', function($query) use ($supplier) {
                $query->where('supplier_id', $supplier->id);
            })
            ->findOrFail($documentId);

        if (!$document->file_path || !Storage::disk('public')->exists($document->file_path)) {
            return redirect()->back()->with('error', 'File tidak ditemukan.');
        }

        return Storage::disk('public')->download($document->file_path, $document->original_filename ?? basename($document->file_path));
    }

    /**
     * Download all approved documents as ZIP
     */
    public function downloadAllApproved($requestId)
    {
        $supplier = $this->getAuthenticatedSupplier();

        $documentRequest = DocumentRequest::where('id', $requestId)
            ->where('supplier_id', $supplier->id)
            ->firstOrFail();

        $approvedDocuments = $documentRequest->requestDocuments()
            ->where('status', 'APPROVED')
            ->whereNotNull('file_path')
            ->get();

        if ($approvedDocuments->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada dokumen yang sudah diapprove.');
        }

        // Create ZIP file
        $zipFileName = 'approved_documents_' . $documentRequest->id . '_' . time() . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);

        // Ensure temp directory exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return redirect()->back()->with('error', 'Gagal membuat file ZIP.');
        }

        foreach ($approvedDocuments as $doc) {
            $filePath = storage_path('app/public/' . $doc->file_path);
            if (file_exists($filePath)) {
                $zip->addFile($filePath, basename($doc->file_path));
            }
        }

        $zip->close();

        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    /**
     * Access document request from email link
     * If user is already logged in as supplier, redirect to request page
     * If not logged in, redirect to login page with intended URL
     */
    public function accessFromEmail($requestId)
    {
        // Check if user is authenticated and is a supplier
        if (auth()->check() && auth()->user()->isSupplier()) {
            $user = auth()->user();
            $supplier = $user->supplier;

            // Check if supplier relationship exists
            if (!$supplier) {
                \Log::error('Supplier relationship is null in accessFromEmail', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'supplier_id' => $user->supplier_id
                ]);
                
                // Force logout
                auth()->logout();
                request()->session()->invalidate();
                request()->session()->regenerateToken();
                
                return redirect()->route('login')
                    ->with('error', 'Akun supplier Anda belum terhubung dengan data supplier. Silakan hubungi administrator.');
            }

            // Verify the request belongs to this supplier
            $documentRequest = DocumentRequest::where('id', $requestId)
                ->where('supplier_id', $supplier->id)
                ->first();

            if ($documentRequest) {
                // Redirect to the request detail page
                return redirect()->route('supplier.request', ['requestId' => $requestId])
                    ->with('success', 'Selamat datang kembali, ' . $user->name . '!');
            } else {
                // Request not found or doesn't belong to this supplier
                return redirect()->route('supplier.dashboard')
                    ->with('error', 'Request dokumen tidak ditemukan atau Anda tidak memiliki akses.');
            }
        }

        // Not logged in - redirect to login with intended URL
        session(['url.intended' => route('supplier.request.access', ['requestId' => $requestId])]);
        
        return redirect()->route('login')
            ->with('info', 'Silakan login terlebih dahulu untuk mengakses dokumen.');
    }

    /**
     * Show document request page for auto-login from email
     * This method is used when supplier clicks link from email
     */
    public function showDocumentRequest($request)
    {
        $supplier = $this->getAuthenticatedSupplier();

        // Verify the request belongs to this supplier
        $documentRequest = DocumentRequest::where('id', $request)
            ->where('supplier_id', $supplier->id)
            ->with([
                'requestParts.requestDocuments.documentType',
                'requestParts.requestDocuments.inspectionStandard',
                'requestParts.requestDocuments.packagingStandard',
                'requestParts.requestDocuments.partSubmissionWarrant',
                'requestParts',
                'supplier',
                'creator'
            ])
            ->firstOrFail();

        // Redirect to the request detail page
        return redirect()->route('supplier.request', ['requestId' => $documentRequest->id]);
    }

    /**
     * Show outstanding/pending documents
     */
    public function outstandingDocuments()
    {
        $supplier = $this->getAuthenticatedSupplier();

        $outstandingRequests = DocumentRequest::where('supplier_id', $supplier->id)
            ->whereHas('requestDocuments', function($query) {
                $query->whereIn('status', ['PENDING', 'REJECTED']);
            })
            ->with(['requestDocuments' => function($query) {
                $query->whereIn('status', ['PENDING', 'REJECTED']);
            }, 'requestDocuments.documentType'])
            ->orderBy('due_date', 'asc')
            ->get();

        return view('supplier.outstanding', compact('outstandingRequests'));
    }

    /**
     * Save draft for a part's documents
     */
    public function saveDraft(Request $request, $partId)
    {
        $supplier = $this->getAuthenticatedSupplier();

        // Verify part belongs to supplier
        $part = \App\Models\RequestPart::where('id', $partId)
            ->whereHas('documentRequest', function($query) use ($supplier) {
                $query->where('supplier_id', $supplier->id);
            })
            ->firstOrFail();

        DB::beginTransaction();
        try {
            // Update part's last saved time and ensure status is DRAFT
            $part->submission_status = 'DRAFT';
            $part->last_saved_at = now();
            $part->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Draft saved successfully. You can continue uploading documents later.',
                'last_saved_at' => $part->last_saved_at->format('d F Y, H:i'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Save draft failed', [
                'part_id' => $partId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to save draft: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit all documents for a part
     */
    public function submitAllDocuments(Request $request, $partId)
    {
        $user = $this->getAuthenticatedUser();
        $supplier = $this->getAuthenticatedSupplier();

        // Verify part belongs to supplier
        $part = \App\Models\RequestPart::where('id', $partId)
            ->whereHas('documentRequest', function($query) use ($supplier) {
                $query->where('supplier_id', $supplier->id);
            })
            ->with(['requestDocuments.documentType'])
            ->firstOrFail();

        // Check if all mandatory documents are uploaded
        $mandatoryDocs = $part->requestDocuments()
            ->whereHas('documentType', function($query) {
                $query->where('is_mandatory', true);
            })
            ->with(['documentType', 'inspectionStandard', 'msaGageRr', 'partSubmissionWarrant', 'packagingStandard'])
            ->get();

        \Log::info('[Submit All] Checking mandatory documents', [
            'part_id' => $partId,
            'total_mandatory' => $mandatoryDocs->count(),
            'documents' => $mandatoryDocs->map(function($doc) {
                return [
                    'id' => $doc->id,
                    'code' => $doc->documentType->code ?? 'UNKNOWN',
                    'status' => $doc->status,
                    'has_form' => !is_null($doc->inspectionStandard ?? $doc->msaGageRr ?? $doc->partSubmissionWarrant ?? $doc->packagingStandard),
                ];
            })->toArray(),
        ]);

        $missingMandatory = $mandatoryDocs->filter(function($doc) {
            $docCode = $doc->documentType->code ?? '';
            
            // Reject if status is REVISE - document must be resubmitted first
            if ($doc->status === 'REVISE') {
                \Log::warning('[Submit All] Document in REVISE status', [
                    'doc_id' => $doc->id,
                    'doc_code' => $docCode,
                    'status' => $doc->status,
                ]);
                return true; // Mark as missing/invalid
            }
            
            // Check based on document type
            if ($docCode === 'IS') {
                // Inspection Standard - check inspection_standards table
                $is = $doc->inspectionStandard;
                $isValid = $is && in_array(strtoupper($is->status), ['SUBMITTED', 'PENDING_REVIEW', 'APPROVED']);
                
                if (!$isValid) {
                    \Log::warning('[Submit All] IS not valid', [
                        'doc_id' => $doc->id,
                        'has_is' => !!$is,
                        'is_status' => $is ? $is->status : 'NO RECORD',
                    ]);
                }
                
                return !$isValid;
            } elseif ($docCode === 'MSA') {
                // MSA Gage R&R - check msa_gage_rr table
                $msa = $doc->msaGageRr;
                $isValid = $msa && in_array(strtoupper($msa->status), ['SUBMITTED', 'PENDING_REVIEW', 'APPROVED']);
                
                if (!$isValid) {
                    \Log::warning('[Submit All] MSA not valid', [
                        'doc_id' => $doc->id,
                        'has_msa' => !!$msa,
                        'msa_status' => $msa ? $msa->status : 'NO RECORD',
                    ]);
                }
                
                return !$isValid;
            } elseif ($docCode === 'PSW') {
                // Part Submission Warrant - check part_submission_warrants table
                $psw = $doc->partSubmissionWarrant;
                $isValid = $psw && in_array(strtoupper($psw->status), ['SUBMITTED', 'PENDING_REVIEW', 'APPROVED']);
                
                if (!$isValid) {
                    \Log::warning('[Submit All] PSW not valid', [
                        'doc_id' => $doc->id,
                        'has_psw' => !!$psw,
                        'psw_status' => $psw ? $psw->status : 'NO RECORD',
                    ]);
                }
                
                return !$isValid;
            } elseif ($docCode === 'PACKAGING_STANDARD') {
                // Packaging Standard - check packaging_standards table
                $ps = $doc->packagingStandard;
                $isValid = $ps && in_array(strtoupper($ps->status), ['SUBMITTED', 'PENDING_REVIEW', 'APPROVED']);
                
                if (!$isValid) {
                    \Log::warning('[Submit All] PS not valid', [
                        'doc_id' => $doc->id,
                        'has_ps' => !!$ps,
                        'ps_status' => $ps ? $ps->status : 'NO RECORD',
                    ]);
                }
                
                return !$isValid;
            } else {
                // Regular upload documents - check request_documents table
                $isValid = in_array(strtoupper($doc->status), ['SUBMITTED', 'APPROVED']) && !empty($doc->file_path);
                
                if (!$isValid) {
                    \Log::warning('[Submit All] Regular doc not valid', [
                        'doc_id' => $doc->id,
                        'doc_code' => $docCode,
                        'status' => $doc->status,
                        'has_file' => !empty($doc->file_path),
                    ]);
                }
                
                return !$isValid;
            }
        });

        if ($missingMandatory->count() > 0) {
            // Check if any documents are in REVISE status
            $revisionDocs = $missingMandatory->filter(function($doc) {
                return $doc->status === 'REVISE';
            });
            
            if ($revisionDocs->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot submit. ' . $revisionDocs->count() . ' mandatory document(s) are marked for revision. Please resubmit the revised documents first.',
                    'revision_documents' => $revisionDocs->map(function($doc) {
                        return [
                            'name' => $doc->documentType->name ?? 'Unknown Document',
                            'revision_note' => $doc->revision_note,
                            'revised_by_team' => $doc->getTeamLabel(),
                        ];
                    })->values()
                ], 400);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Cannot submit. ' . $missingMandatory->count() . ' mandatory document(s) are still missing or not submitted.',
                'missing_documents' => $missingMandatory->pluck('documentType.name')
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Update part submission status
            $part->submission_status = 'SUBMITTED';
            $part->submitted_at = now();
            $part->submitted_by = $user->id;
            $part->save();

            // Update all uploaded documents to SUBMITTED status if they are still PENDING
            $part->requestDocuments()
                ->where('status', 'PENDING')
                ->whereNotNull('file_path')
                ->update([
                    'status' => 'SUBMITTED',
                    'uploaded_at' => now(),
                    'submitted_at' => now()
                ]);

            // Create history records for documents and ensure submitted_at is set
            foreach ($part->requestDocuments as $doc) {
                if ($doc->file_path) {
                    // Set submitted_at jika belum terisi
                    if (!$doc->submitted_at) {
                        $doc->submitted_at = now();
                        $doc->save();
                    }
                    
                    $doc->histories()->create([
                        'action' => 'SUBMITTED',
                        'actor_id' => $user->id,
                        'actor_name' => $user->name,
                        'note' => 'Document submitted for approval by supplier'
                    ]);
                }
            }

            // Send notification to Akebono (reuse existing job)
            $submittedDocs = $part->requestDocuments()
                ->where('status', 'SUBMITTED')
                ->orWhere(function($query) {
                    $query->where('status', 'PENDING')->whereNotNull('file_path');
                })
                ->with('documentType')
                ->get();
            
            $submittedDocumentsData = $submittedDocs->map(function($doc) {
                return [
                    'code' => $doc->documentType->code ?? '',
                    'name' => $doc->documentType->name ?? ''
                ];
            })->toArray();
            
            DocumentsSubmittedToAkebonoJob::dispatch(
                $part->documentRequest,
                $submittedDocs->count(),
                $submittedDocumentsData
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'All documents submitted successfully! Your documents are now under review by Akebono team.',
                'submitted_at' => $part->submitted_at->format('d F Y, H:i'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Submit documents failed', [
                'part_id' => $partId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit documents: ' . $e->getMessage()
            ], 500);
        }
    }
}
