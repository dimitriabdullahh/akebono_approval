<?php

namespace App\Services;

use App\Models\CheckerSignature;
use App\Models\DocumentHistory;
use App\Models\DocumentSignature;
use App\Models\Employee;
use App\Models\RequestDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckerWorkflowService
{
    private QRCodeService $qrCodeService;
    private NotificationService $notificationService;
    private SignatureQrService $signatureQrService;

    public function __construct(
        QRCodeService $qrCodeService,
        NotificationService $notificationService,
        SignatureQrService $signatureQrService
    ) {
        $this->qrCodeService = $qrCodeService;
        $this->notificationService = $notificationService;
        $this->signatureQrService = $signatureQrService;
    }

    /**
     * Complete checking process and forward to Approver
     *
     * @param RequestDocument $requestDocument
     * @param Employee $checker
     * @param string|null $notes
     * @return bool
     * @throws \Exception
     */
    public function completeCheck(RequestDocument $requestDocument, Employee $checker, ?string $notes = null): bool
    {
        // Validate checker can check this document
        if (!$this->canCheck($requestDocument, $checker)) {
            throw new \Exception('You do not have permission to check this document');
        }

        // Validate status
        if (!in_array($requestDocument->status, ['SUBMITTED', 'RECEIVED'])) {
            throw new \Exception('Document is not in a valid status for checking');
        }

        // Validate checker has NPK
        if (empty($checker->npk)) {
            throw new \Exception('Checker must have NPK to sign the document');
        }

        DB::beginTransaction();
        try {
            // Generate old checker signature (for compatibility)
            $signature = CheckerSignature::generate(
                $requestDocument,
                $checker,
                CheckerSignature::TYPE_CHECKER
            );

            // Create new DocumentSignature with QR code
            // First, delete any existing checker signature for this document (for revision case)
            DocumentSignature::where('request_document_id', $requestDocument->id)
                ->where('role_type', 'checker')
                ->delete();

            // Create new checker e-signature
            $documentSignature = DocumentSignature::create([
                'request_document_id' => $requestDocument->id,
                'role_type' => 'checker',
                'employee_id' => $checker->id,
                'npk' => $checker->npk, // Snapshot
                'name' => $checker->name, // Snapshot
                'role_label' => 'Checker',
                'notes' => $notes,
                'signed_at' => now(),
            ]);

            // Update request document with checker info
            $requestDocument->update([
                'checked_by_employee_id' => $checker->id,
                'checked_at' => now(),
                'checker_notes' => $notes,
                'checker_esign_token' => $signature->token,
                'workflow_stage' => 'CHECKED',
            ]);

            // Record history
            DocumentHistory::create([
                'request_document_id' => $requestDocument->id,
                'action' => 'CHECKED',
                'actor_id' => $checker->id,
                'actor_name' => $checker->name,
                'note' => "Document checked by {$checker->division} {$checker->role}" . ($notes ? ": {$notes}" : ''),
            ]);

            // Generate PDF with QR code embedded (old method, keep for compatibility)
            $this->embedCheckerQRCode($requestDocument, $signature);

            // Forward to Approver
            $this->forwardToApprover($requestDocument);

            DB::commit();

            Log::info('Document check completed with e-signature', [
                'request_document_id' => $requestDocument->id,
                'checker_id' => $checker->id,
                'checker_name' => $checker->name,
                'checker_npk' => $checker->npk,
                'signature_token' => $signature->token,
                'document_signature_id' => $documentSignature->id,
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to complete document check', [
                'request_document_id' => $requestDocument->id,
                'checker_id' => $checker->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Request revision from supplier
     *
     * @param RequestDocument $requestDocument
     * @param Employee $checker
     * @param string $revisionNote
     * @return bool
     * @throws \Exception
     */
    public function requestRevision(RequestDocument $requestDocument, Employee $checker, string $revisionNote): bool
    {
        // Validate checker can check this document
        if (!$this->canCheck($requestDocument, $checker)) {
            throw new \Exception('You do not have permission to request revision for this document');
        }

        // Validate status
        if (!in_array($requestDocument->status, ['SUBMITTED', 'RECEIVED'])) {
            throw new \Exception('Document is not in a valid status for revision request');
        }

        DB::beginTransaction();
        try {
            // Calculate revision deadline
            $revisionDays = (int) \App\Models\RevisionConfig::where('key', 'revision_deadline_days')->first()?->value ?? 2;
            $revisionDeadline = now()->addDays($revisionDays);

            // Update document status
            $requestDocument->update([
                'status' => 'REVISE',
                'revision_note' => $revisionNote,
                'revision_deadline_at' => $revisionDeadline,
                'workflow_stage' => 'REVISION',
            ]);

            // Record history
            DocumentHistory::create([
                'request_document_id' => $requestDocument->id,
                'action' => 'REQUEST_REVISION',
                'actor_id' => $checker->id,
                'actor_name' => $checker->name,
                'note' => "Revision requested by {$checker->division} {$checker->role}: {$revisionNote}",
            ]);

            // Send notification to supplier
            $this->notificationService->notifyRevisionRequested($requestDocument);

            DB::commit();

            Log::info('Revision requested', [
                'request_document_id' => $requestDocument->id,
                'checker_id' => $checker->id,
                'revision_note' => $revisionNote,
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to request revision', [
                'request_document_id' => $requestDocument->id,
                'checker_id' => $checker->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Receive document (for IS workflow)
     *
     * @param RequestDocument $requestDocument
     * @param Employee $receiver
     * @return bool
     * @throws \Exception
     */
    public function receiveDocument(RequestDocument $requestDocument, Employee $receiver): bool
    {
        // Validate this is IS document
        if ($requestDocument->documentType->code !== 'IS') {
            throw new \Exception('Only Inspection Standard documents require receiving step');
        }

        // Validate status
        if ($requestDocument->status !== 'SUBMITTED') {
            throw new \Exception('Document must be in SUBMITTED status to be received');
        }

        DB::beginTransaction();
        try {
            // Generate receiver signature
            $signature = CheckerSignature::generate(
                $requestDocument,
                $receiver,
                CheckerSignature::TYPE_RECEIVER
            );

            // Update document
            $requestDocument->update([
                'received_by_employee_id' => $receiver->id,
                'received_at' => now(),
                'receiver_esign_token' => $signature->token,
                'workflow_stage' => 'RECEIVED',
                'status' => 'RECEIVED',
            ]);

            // Record history
            DocumentHistory::create([
                'request_document_id' => $requestDocument->id,
                'action' => 'RECEIVED',
                'actor_id' => $receiver->id,
                'actor_name' => $receiver->name,
                'note' => "Document received by {$receiver->division} {$receiver->role}",
            ]);

            // Embed receiver QR code in PDF
            $this->embedReceiverQRCode($requestDocument, $signature);

            DB::commit();

            Log::info('Document received', [
                'request_document_id' => $requestDocument->id,
                'receiver_id' => $receiver->id,
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to receive document', [
                'request_document_id' => $requestDocument->id,
                'receiver_id' => $receiver->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Check if employee can check this document
     *
     * @param RequestDocument $requestDocument
     * @param Employee $employee
     * @return bool
     */
    public function canCheck(RequestDocument $requestDocument, Employee $employee): bool
    {
        // Must be active and Checker role
        if (!$employee->is_active || !$employee->isChecker()) {
            return false;
        }

        // Get document scopes
        $scopes = $requestDocument->documentType->scope ?? [];
        
        // Map employee division to document scope
        $employeeScope = $employee->getReviewScope();
        
        if (!$employeeScope) {
            return false;
        }

        return in_array($employeeScope, $scopes);
    }

    /**
     * Embed Checker QR code into PDF document
     *
     * @param RequestDocument $requestDocument
     * @param CheckerSignature $signature
     * @return void
     */
    private function embedCheckerQRCode(RequestDocument $requestDocument, CheckerSignature $signature): void
    {
        // Generate QR code as base64
        $qrCodeBase64 = $this->qrCodeService->generateForSignature($signature);

        // Store QR code data for PDF generation
        // The actual PDF generation with QR code will be handled by respective controllers
        // (InspectionStandardController, PackagingStandardController, PartSubmissionWarrantController)
        
        Log::info('QR code generated for checker signature', [
            'signature_id' => $signature->id,
            'request_document_id' => $requestDocument->id,
        ]);
    }

    /**
     * Embed Receiver QR code into PDF document (for IS)
     *
     * @param RequestDocument $requestDocument
     * @param CheckerSignature $signature
     * @return void
     */
    private function embedReceiverQRCode(RequestDocument $requestDocument, CheckerSignature $signature): void
    {
        // Generate QR code as base64
        $qrCodeBase64 = $this->qrCodeService->generateForSignature($signature);

        Log::info('QR code generated for receiver signature', [
            'signature_id' => $signature->id,
            'request_document_id' => $requestDocument->id,
        ]);
    }

    /**
     * Forward document to appropriate Approver after check completion
     *
     * @param RequestDocument $requestDocument
     * @return void
     */
    private function forwardToApprover(RequestDocument $requestDocument): void
    {
        // Update workflow stage
        $requestDocument->update([
            'workflow_stage' => 'APPROVING',
        ]);

        // Send notification to Approvers
        // Get approvers based on document scope
        $scopes = $requestDocument->documentType->scope ?? [];
        
        foreach ($scopes as $scope) {
            $approvers = Employee::active()
                ->approvers()
                ->where('division', $this->mapScopeToDivision($scope))
                ->get();

            foreach ($approvers as $approver) {
                // Send email notification
                try {
                    \Mail::to($approver->email)->send(
                        new \App\Mail\DocumentReadyForApproval($requestDocument, $approver)
                    );
                } catch (\Exception $e) {
                    Log::error('Failed to send approval notification', [
                        'approver_id' => $approver->id,
                        'request_document_id' => $requestDocument->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        Log::info('Document forwarded to approvers', [
            'request_document_id' => $requestDocument->id,
            'scopes' => $scopes,
        ]);
    }

    /**
     * Map scope to division name
     *
     * @param string $scope
     * @return string
     */
    private function mapScopeToDivision(string $scope): string
    {
        return match($scope) {
            'QA' => 'QA',
            'PURCHASING' => 'Purchasing',
            'PPC' => 'PCC',
            default => $scope,
        };
    }

    /**
     * Get pending documents for Checker
     *
     * @param Employee $checker
     * @return \Illuminate\Support\Collection
     */
    public function getPendingDocumentsForChecker(Employee $checker)
    {
        if (!$checker->isChecker()) {
            return collect();
        }

        $scope = $checker->getReviewScope();
        if (!$scope) {
            return collect();
        }

        // Get documents that are SUBMITTED or RECEIVED (for IS) and need checking
        $statuses = $checker->division === 'QA' 
            ? ['SUBMITTED', 'RECEIVED'] 
            : ['SUBMITTED'];

        return RequestDocument::whereIn('status', $statuses)
            ->whereNull('checked_by_employee_id')
            ->whereHas('documentType', function ($q) use ($scope) {
                $q->whereJsonContains('scope', $scope);
            })
            ->with(['documentRequest.supplier', 'documentType', 'histories'])
            ->orderByDesc('submitted_at')
            ->get();
    }
}
