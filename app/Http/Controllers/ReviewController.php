<?php

namespace App\Http\Controllers;

use App\Models\RequestDocument;
use App\Services\NotificationService;
use App\Services\ReviewApprovalService;
use App\Services\CheckerWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    private ReviewApprovalService $reviewService;
    private NotificationService $notificationService;
    private CheckerWorkflowService $checkerService;

    public function __construct(
        ReviewApprovalService $reviewService,
        NotificationService $notificationService,
        CheckerWorkflowService $checkerService
    ) {
        $this->middleware('auth');
        $this->middleware('internal_admin');
        $this->reviewService = $reviewService;
        $this->notificationService = $notificationService;
        $this->checkerService = $checkerService;
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
        // Check authorization berdasarkan scope
        if (!$this->reviewService->userCanReviewDocument(auth()->id(), $requestDocument)) {
            abort(403, 'Anda tidak memiliki akses untuk mereview dokumen dengan scope ini');
        }

        // For form-based documents with SUBMITTED status, redirect to universal preview
        $docCode = $requestDocument->documentType->code ?? '';
        $isFormBased = in_array($docCode, ['IS', 'PACKAGING_STANDARD', 'PSW', 'MSA']);
        
        if ($isFormBased && in_array($requestDocument->status, ['SUBMITTED', 'PENDING_REVIEW'])) {
            // Redirect to universal preview page with review tab active
            return redirect()->route('admin.document.preview', ['requestDocumentId' => $requestDocument->id])
                            ->with('activeTab', 'review');
        }

        // Original logic for non-form-based documents
        $requestDocument->load([
            'documentRequest.supplier',
            'documentType',
            'approvals',
            'histories.actor',
        ]);

        // Ensure documentType scope is properly cast to array
        if ($requestDocument->documentType && is_string($requestDocument->documentType->scope)) {
            $requestDocument->documentType->scope = json_decode($requestDocument->documentType->scope, true) ?? [];
        }

        // Load form-based document data (for non-redirect cases)
        $inspectionStandard = null;
        $packagingStandard = null;
        $partSubmissionWarrant = null;
        $msaGageRr = null;

        if ($requestDocument->documentType->code === 'IS') {
            $inspectionStandard = $requestDocument->inspectionStandard;
            if ($inspectionStandard) {
                $inspectionStandard->load([
                    'drawings',
                    'inspectionItems',
                    'supplier',
                ]);
            }
        } elseif ($requestDocument->documentType->code === 'PACKAGING_STANDARD') {
            $packagingStandard = $requestDocument->packagingStandard;
            if ($packagingStandard) {
                $packagingStandard->load(['supplier']);
            }
        } elseif ($requestDocument->documentType->code === 'PSW') {
            $partSubmissionWarrant = $requestDocument->partSubmissionWarrant;
            if ($partSubmissionWarrant) {
                $partSubmissionWarrant->load(['supplier']);
            }
        } elseif ($requestDocument->documentType->code === 'MSA') {
            $msaGageRr = $requestDocument->msaGageRr;
            if ($msaGageRr) {
                $msaGageRr->load(['supplier']);
            }
        }

        $user = auth()->guard('employee')->check() 
            ? auth()->guard('employee')->user() 
            : auth()->user();
        $userScope = $user ? $user->getReviewScope() : null;
        
        // Determine if current user is Checker or Approver
        $isChecker = false;
        $isApprover = false;
        $employee = null;
        
        if (auth()->guard('employee')->check()) {
            $employee = auth()->guard('employee')->user();
            $isChecker = $employee->isChecker();
            $isApprover = $employee->isApprover();
        }

        return view('admin.review.show', compact(
            'requestDocument', 
            'userScope', 
            'inspectionStandard',
            'packagingStandard',
            'partSubmissionWarrant',
            'msaGageRr',
            'isChecker',
            'isApprover',
            'employee'
        ));
    }

    /**
     * Approve dokumen
     */
    public function approve(RequestDocument $requestDocument)
    {
        // Check employee authentication
        if (!auth()->guard('employee')->check()) {
            abort(403, 'Employee authentication required');
        }

        $employee = auth()->guard('employee')->user();

        // Validate Approver role
        if (!$employee->isApprover()) {
            abort(403, 'Only Approver can approve documents');
        }

        // Validate employee has NPK
        if (empty($employee->npk)) {
            return back()->with('error', 'Approver must have NPK to sign the document');
        }

        if ($requestDocument->status !== 'SUBMITTED') {
            return back()->with('error', 'Dokumen sudah tidak dalam status SUBMITTED');
        }

        DB::beginTransaction();
        try {
            // Create Approver E-Signature
            // Delete any existing approver signature for this document (for revision case)
            \App\Models\DocumentSignature::where('request_document_id', $requestDocument->id)
                ->where('role_type', 'approver')
                ->delete();

            // Create new approver e-signature
            $documentSignature = \App\Models\DocumentSignature::create([
                'request_document_id' => $requestDocument->id,
                'role_type' => 'approver',
                'employee_id' => $employee->id,
                'npk' => $employee->npk, // Snapshot
                'name' => $employee->name, // Snapshot
                'role_label' => 'Approver',
                'notes' => null,
                'signed_at' => now(),
            ]);

            // Get employee scope (division)
            $scope = strtoupper($employee->division); // QA, PPC, or Purchasing

            // Create or update DocumentApproval record
            \App\Models\DocumentApproval::updateOrCreate(
                [
                    'request_document_id' => $requestDocument->id,
                    'scope' => $scope,
                ],
                [
                    'employee_id' => $employee->id,
                    'user_id' => null, // New workflow uses employee_id, not user_id
                    'division' => $employee->division,
                    'role' => 'Approver',
                    'document_type' => $requestDocument->documentType->code ?? null,
                    'status' => 'APPROVED',
                    'approved_at' => now(),
                ]
            );

            // Record history
            \App\Models\DocumentHistory::create([
                'request_document_id' => $requestDocument->id,
                'action' => 'APPROVE',
                'actor_id' => $employee->id,
                'actor_name' => $employee->name,
                'note' => "Dokumen disetujui oleh {$scope} Approver dengan e-signature",
            ]);

            // Check if all scopes approved
            if ($requestDocument->allScopesApproved()) {
                $requestDocument->update([
                    'status' => 'APPROVED',
                    'reviewed_at' => now(),
                ]);

                \App\Models\DocumentHistory::create([
                    'request_document_id' => $requestDocument->id,
                    'action' => 'APPROVED_FINAL',
                    'actor_id' => $employee->id,
                    'actor_name' => 'System',
                    'note' => 'Semua scope telah menyetujui. Status dokumen berubah menjadi APPROVED',
                ]);

                // Notify completion
                if ($this->reviewService->allMandatoryDocumentsApproved($requestDocument->documentRequest)) {
                    $this->notificationService->notifyApprovalCompleted($requestDocument->documentRequest);
                }
            } else {
                $requestDocument->update([
                    'reviewed_at' => now(),
                ]);
            }

            DB::commit();

            \Log::info('Document approved with e-signature', [
                'request_document_id' => $requestDocument->id,
                'approver_id' => $employee->id,
                'approver_name' => $employee->name,
                'approver_npk' => $employee->npk,
                'scope' => $scope,
                'document_signature_id' => $documentSignature->id,
            ]);

            return back()->with('success', 'Dokumen berhasil di-approve dan e-signature telah terbubuh');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to approve document with e-signature', [
                'request_document_id' => $requestDocument->id,
                'approver_id' => $employee->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Gagal approve dokumen: ' . $e->getMessage());
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

    /**
     * Complete checking (Checker action)
     */
    public function completeCheck(Request $request, RequestDocument $requestDocument)
    {
        // Validate employee authentication
        if (!auth()->guard('employee')->check()) {
            abort(403, 'Employee authentication required');
        }

        $employee = auth()->guard('employee')->user();

        // Validate Checker role
        if (!$employee->isChecker()) {
            abort(403, 'Only Checker can complete document checking');
        }

        // Validate optional notes
        $validated = $request->validate([
            'checker_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->checkerService->completeCheck(
                $requestDocument,
                $employee,
                $validated['checker_notes'] ?? null
            );

            return back()->with('success', 'Dokumen berhasil di-check dan diteruskan ke Approver');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Request revision (Checker action)
     */
    public function checkerRequestRevision(Request $request, RequestDocument $requestDocument)
    {
        // Validate employee authentication
        if (!auth()->guard('employee')->check()) {
            abort(403, 'Employee authentication required');
        }

        $employee = auth()->guard('employee')->user();

        // Validate Checker role
        if (!$employee->isChecker()) {
            abort(403, 'Only Checker can request revision');
        }

        $validated = $request->validate([
            'revision_note' => 'required|string|max:1000',
        ]);

        try {
            $this->checkerService->requestRevision(
                $requestDocument,
                $employee,
                $validated['revision_note']
            );

            return back()->with('success', 'Revisi berhasil diminta. Notifikasi telah dikirim ke supplier');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Receive document (for IS workflow)
     */
    public function receiveDocument(RequestDocument $requestDocument)
    {
        // Validate employee authentication
        if (!auth()->guard('employee')->check()) {
            abort(403, 'Employee authentication required');
        }

        $employee = auth()->guard('employee')->user();

        // Validate QA division
        if (!$employee->isQA()) {
            abort(403, 'Only QA division can receive documents');
        }

        try {
            $this->checkerService->receiveDocument($requestDocument, $employee);

            return back()->with('success', 'Dokumen berhasil diterima');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
