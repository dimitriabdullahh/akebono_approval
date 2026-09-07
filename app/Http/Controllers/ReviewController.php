<?php

namespace App\Http\Controllers;

use App\Models\RequestDocument;
use App\Services\NotificationService;
use App\Services\ReviewApprovalService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    private ReviewApprovalService $reviewService;
    private NotificationService $notificationService;

    public function __construct(
        ReviewApprovalService $reviewService,
        NotificationService $notificationService
    ) {
        $this->middleware('auth');
        $this->middleware('internal_admin');
        $this->reviewService = $reviewService;
        $this->notificationService = $notificationService;
    }

    /**
     * Tampilkan daftar dokumen untuk di-review
     */
    public function index(Request $request)
    {
        // Get documents yang pending review (SUBMITTED)
        $pendingDocuments = $this->reviewService->getPendingDocumentsForReview(auth()->id());
        
        // Get documents yang perlu revisi (REVISE)
        $revisionDocuments = $this->reviewService->getRevisionDocumentsForReview(auth()->id());
        
        // Get approved documents
        $approvedDocuments = $this->reviewService->getApprovedDocumentsForReview(auth()->id());
        
        $totalDocuments = $pendingDocuments->count() + $revisionDocuments->count() + $approvedDocuments->count();

        return view('admin.review.index', compact(
            'pendingDocuments',
            'revisionDocuments',
            'approvedDocuments',
            'totalDocuments'
        ));
    }

    /**
     * Tampilkan detail dokumen untuk review
     * Dengan pengecekan scope sesuai Implementation Document
     */
    public function show(RequestDocument $requestDocument)
    {
        // Check authorization berdasarkan scope - middleware scope.check sudah menangani ini
        // Tapi kita tetap double-check untuk keamanan
        if (!$this->reviewService->userCanReviewDocument(auth()->id(), $requestDocument)) {
            abort(403, 'Anda tidak memiliki akses untuk mereview dokumen dengan scope ini');
        }

        $requestDocument->load([
            'documentRequest.supplier',
            'documentType',
            'histories.actor',
        ]);

        // Load Inspection Standard data if this is an IS document
        $inspectionStandard = null;
        if ($requestDocument->documentType->code === 'IS') {
            $inspectionStandard = $requestDocument->inspectionStandard;
            if ($inspectionStandard) {
                $inspectionStandard->load([
                    'drawings',
                    'inspectionItems',
                    'supplier',
                ]);
            }
        }

        $user = auth()->user();
        $userScope = $user->getReviewScope();

        return view('admin.review.show', compact('requestDocument', 'userScope', 'inspectionStandard'));
    }

    /**
     * Approve dokumen
     */
    public function approve(RequestDocument $requestDocument)
    {
        // Check authorization
        if (!$this->reviewService->userCanReviewDocument(auth()->id(), $requestDocument)) {
            abort(403);
        }

        if ($requestDocument->status !== 'SUBMITTED') {
            return back()->with('error', 'Dokumen sudah tidak dalam status SUBMITTED');
        }

        try {
            $this->reviewService->approveDocument($requestDocument, auth()->id());

            // Check jika semua mandatory docs sudah approved
            if ($this->reviewService->allMandatoryDocumentsApproved($requestDocument->documentRequest)) {
                $this->notificationService->notifyApprovalCompleted($requestDocument->documentRequest);
            }

            return back()->with('success', 'Dokumen berhasil di-approve');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject / request revisi dokumen
     */
    public function reject(Request $request, RequestDocument $requestDocument)
    {
        // Check authorization
        if (!$this->reviewService->userCanReviewDocument(auth()->id(), $requestDocument)) {
            abort(403);
        }

        if ($requestDocument->status !== 'SUBMITTED') {
            return back()->with('error', 'Dokumen sudah tidak dalam status SUBMITTED');
        }

        $validated = $request->validate([
            'revision_note' => 'required|string|max:1000',
        ]);

        try {
            $this->reviewService->rejectDocument(
                $requestDocument,
                $validated['revision_note'],
                auth()->id()
            );

            return back()->with('success', 'Dokumen diminta untuk direvisi');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
