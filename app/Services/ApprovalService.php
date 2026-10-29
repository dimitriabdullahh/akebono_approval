<?php

namespace App\Services;

use App\Models\ApprovalToken;
use App\Models\DocumentApproval;
use App\Models\RequestDocument;
use App\Models\DocumentHistory;
use App\Models\ApprovalSignature;
use App\Mail\ApprovalRequestMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ApprovalService
{
    /**
     * Create approval tasks for PSW after Checker QA passes
     * Creates tasks for QA and Purchasing (parallel approval)
     * 
     * @param RequestDocument $requestDocument
     * @param int $revisionNumber
     * @return array ['qa' => DocumentApproval, 'purchasing' => DocumentApproval]
     */
    public function createPSWApprovalTasks(RequestDocument $requestDocument, int $revisionNumber = 1): array
    {
        DB::beginTransaction();
        
        try {
            // Create QA approval task
            $qaApproval = DocumentApproval::create([
                'request_document_id' => $requestDocument->id,
                'scope' => 'QA',
                'division' => 'QA',
                'role' => 'APPROVER',
                'document_type' => 'PSW',
                'revision_number' => $revisionNumber,
                'status' => 'PENDING',
            ]);
            
            // Create Purchasing approval task
            $purchasingApproval = DocumentApproval::create([
                'request_document_id' => $requestDocument->id,
                'scope' => 'PURCHASING',
                'division' => 'PURCHASING',
                'role' => 'APPROVER',
                'document_type' => 'PSW',
                'revision_number' => $revisionNumber,
                'status' => 'PENDING',
            ]);
            
            Log::info('[ApprovalService] PSW approval tasks created', [
                'request_document_id' => $requestDocument->id,
                'qa_approval_id' => $qaApproval->id,
                'purchasing_approval_id' => $purchasingApproval->id,
                'revision_number' => $revisionNumber,
            ]);
            
            DB::commit();
            
            return [
                'qa' => $qaApproval,
                'purchasing' => $purchasingApproval,
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[ApprovalService] Failed to create PSW approval tasks', [
                'error' => $e->getMessage(),
                'request_document_id' => $requestDocument->id,
            ]);
            throw $e;
        }
    }
    
    /**
     * Create approval tasks for PS after supplier submits
     * Creates tasks for QA, Purchasing, and PPC (parallel approval)
     * 
     * @param RequestDocument $requestDocument
     * @param int $revisionNumber
     * @return array ['qa' => DocumentApproval, 'purchasing' => DocumentApproval, 'ppc' => DocumentApproval]
     */
    public function createPSApprovalTasks(RequestDocument $requestDocument, int $revisionNumber = 1): array
    {
        DB::beginTransaction();
        
        try {
            // Create QA approval task
            $qaApproval = DocumentApproval::create([
                'request_document_id' => $requestDocument->id,
                'scope' => 'QA',
                'division' => 'QA',
                'role' => 'APPROVER',
                'document_type' => 'PACKAGING_STANDARD',
                'revision_number' => $revisionNumber,
                'status' => 'PENDING',
            ]);
            
            // Create Purchasing approval task
            $purchasingApproval = DocumentApproval::create([
                'request_document_id' => $requestDocument->id,
                'scope' => 'PURCHASING',
                'division' => 'PURCHASING',
                'role' => 'APPROVER',
                'document_type' => 'PACKAGING_STANDARD',
                'revision_number' => $revisionNumber,
                'status' => 'PENDING',
            ]);
            
            // Create PPC approval task
            $ppcApproval = DocumentApproval::create([
                'request_document_id' => $requestDocument->id,
                'scope' => 'PPC',
                'division' => 'PPC',
                'role' => 'APPROVER',
                'document_type' => 'PACKAGING_STANDARD',
                'revision_number' => $revisionNumber,
                'status' => 'PENDING',
            ]);
            
            Log::info('[ApprovalService] PS approval tasks created', [
                'request_document_id' => $requestDocument->id,
                'qa_approval_id' => $qaApproval->id,
                'purchasing_approval_id' => $purchasingApproval->id,
                'ppc_approval_id' => $ppcApproval->id,
                'revision_number' => $revisionNumber,
            ]);
            
            DB::commit();
            
            return [
                'qa' => $qaApproval,
                'purchasing' => $purchasingApproval,
                'ppc' => $ppcApproval,
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[ApprovalService] Failed to create PS approval tasks', [
                'error' => $e->getMessage(),
                'request_document_id' => $requestDocument->id,
            ]);
            throw $e;
        }
    }
    
    /**
     * Generate approval token and send email for external approval
     * 
     * @param DocumentApproval $approval
     * @param string $recipientEmail
     * @param string $recipientName
     * @param array $options
     * @return ApprovalToken
     */
    public function generateTokenAndSendEmail(
        DocumentApproval $approval,
        string $recipientEmail,
        string $recipientName,
        array $options = []
    ): ApprovalToken {
        try {
            // Generate secure token
            $token = ApprovalToken::generate(
                $approval,
                $recipientEmail,
                $recipientName,
                $options
            );
            
            // Send email notification
            Mail::to($recipientEmail)->send(
                new ApprovalRequestMail($approval, $token)
            );
            
            Log::info('[ApprovalService] Approval email sent', [
                'approval_id' => $approval->id,
                'division' => $approval->division,
                'recipient' => $recipientEmail,
                'token_id' => $token->id,
            ]);
            
            return $token;
            
        } catch (\Exception $e) {
            Log::error('[ApprovalService] Failed to send approval email', [
                'error' => $e->getMessage(),
                'approval_id' => $approval->id,
                'recipient' => $recipientEmail,
            ]);
            throw $e;
        }
    }
    
    /**
     * Process approval decision via token (email-based approval)
     * 
     * @param ApprovalToken $token
     * @param string $decision 'APPROVED' or 'REJECTED'
     * @param array $data
     * @return array ['success' => bool, 'message' => string, 'approval' => DocumentApproval]
     */
    public function processTokenApproval(
        ApprovalToken $token,
        string $decision,
        array $data = []
    ): array {
        DB::beginTransaction();
        
        try {
            // Validate token
            $validation = $token->validate();
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['reason'],
                    'approval' => null,
                ];
            }
            
            $approval = $token->documentApproval;
            
            // Check if approval is still pending
            if ($approval->status !== 'PENDING') {
                return [
                    'success' => false,
                    'message' => 'This approval has already been processed',
                    'approval' => $approval,
                ];
            }
            
            // Update approval status
            $approval->update([
                'status' => $decision,
                'approved_at' => $decision === 'APPROVED' ? Carbon::now() : null,
                'note' => $data['note'] ?? null,
                'comment' => $data['comment'] ?? null,
            ]);
            
            // Mark token as used
            $token->markAsUsed();
            
            // Create document history
            DocumentHistory::create([
                'request_document_id' => $approval->request_document_id,
                'actor_type' => 'email_approval',
                'actor_id' => $token->id,
                'action' => $decision === 'APPROVED' ? 'APPROVED' : 'REJECTED',
                'description' => sprintf(
                    '%s approval %s via email by %s (%s)',
                    $approval->division,
                    $decision === 'APPROVED' ? 'approved' : 'rejected',
                    $token->recipient_name ?? $token->recipient_email,
                    $token->recipient_email
                ),
                'metadata' => json_encode([
                    'division' => $approval->division,
                    'scope' => $approval->scope,
                    'recipient_email' => $token->recipient_email,
                    'recipient_name' => $token->recipient_name,
                    'token_id' => $token->id,
                    'note' => $data['note'] ?? null,
                ]),
            ]);
            
            // If approved, generate QR code signature
            if ($decision === 'APPROVED') {
                $this->generateApprovalSignature($approval, $token);
            }
            
            // Check if all approvals are complete
            $this->checkAndFinalizeDocument($approval->requestDocument);
            
            DB::commit();
            
            Log::info('[ApprovalService] Token approval processed', [
                'approval_id' => $approval->id,
                'decision' => $decision,
                'division' => $approval->division,
                'recipient' => $token->recipient_email,
            ]);
            
            return [
                'success' => true,
                'message' => $decision === 'APPROVED' 
                    ? 'Document approved successfully' 
                    : 'Document rejected successfully',
                'approval' => $approval,
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[ApprovalService] Failed to process token approval', [
                'error' => $e->getMessage(),
                'token_id' => $token->id,
            ]);
            throw $e;
        }
    }
    
    /**
     * Generate QR code approval signature
     * 
     * @param DocumentApproval $approval
     * @param ApprovalToken $token
     * @return ApprovalSignature
     */
    protected function generateApprovalSignature(
        DocumentApproval $approval,
        ApprovalToken $token
    ): ApprovalSignature {
        // Generate verification token for QR code
        $verificationToken = hash('sha256', uniqid($approval->id . time(), true));
        
        $signature = ApprovalSignature::create([
            'approval_id' => $approval->id,
            'signer_name' => $token->recipient_name ?? 'External Approver',
            'signer_email' => $token->recipient_email,
            'signer_npk' => null, // Email approval doesn't have NPK
            'division' => $approval->division,
            'role' => $approval->role,
            'signature_type' => 'EMAIL_APPROVAL',
            'verification_token' => $verificationToken,
            'signed_at' => Carbon::now(),
            'ip_address' => $token->ip_address,
            'user_agent' => $token->user_agent,
        ]);
        
        Log::info('[ApprovalService] Approval signature generated', [
            'signature_id' => $signature->id,
            'approval_id' => $approval->id,
            'division' => $approval->division,
        ]);
        
        return $signature;
    }
    
    /**
     * Check if all required approvals are complete and finalize document
     * 
     * @param RequestDocument $requestDocument
     * @return bool
     */
    public function checkAndFinalizeDocument(RequestDocument $requestDocument): bool
    {
        $documentType = $requestDocument->documentType->code;
        
        // Get all approvals for current revision
        $approvals = DocumentApproval::where('request_document_id', $requestDocument->id)
            ->where('revision_number', $this->getCurrentRevisionNumber($requestDocument))
            ->get();
        
        $requiredApprovals = [];
        
        // Determine required approvals based on document type
        if ($documentType === 'PSW') {
            $requiredApprovals = ['QA', 'PURCHASING'];
        } elseif ($documentType === 'PACKAGING_STANDARD') {
            $requiredApprovals = ['QA', 'PURCHASING', 'PPC'];
        } else {
            // Other document types, check scope from document type
            $requiredApprovals = $requestDocument->documentType->scope ?? [];
        }
        
        // Check if all required approvals are APPROVED
        $completedApprovals = $approvals->where('status', 'APPROVED')->pluck('division')->toArray();
        
        foreach ($requiredApprovals as $required) {
            if (!in_array($required, $completedApprovals)) {
                Log::info('[ApprovalService] Not all approvals complete yet', [
                    'request_document_id' => $requestDocument->id,
                    'required' => $requiredApprovals,
                    'completed' => $completedApprovals,
                ]);
                return false;
            }
        }
        
        // All required approvals complete - finalize document
        $requestDocument->update([
            'status' => 'APPROVED',
            'reviewed_at' => Carbon::now(),
        ]);
        
        DocumentHistory::create([
            'request_document_id' => $requestDocument->id,
            'action' => 'FINALIZED',
            'description' => 'Document fully approved by all required divisions',
            'metadata' => json_encode([
                'approved_by' => $completedApprovals,
                'document_type' => $documentType,
            ]),
        ]);
        
        Log::info('[ApprovalService] Document finalized - all approvals complete', [
            'request_document_id' => $requestDocument->id,
            'approved_by' => $completedApprovals,
        ]);
        
        // TODO: Send notification to supplier
        
        return true;
    }
    
    /**
     * Request revision via token (email-based)
     * 
     * @param ApprovalToken $token
     * @param string $reason
     * @return array
     */
    public function requestRevisionViaToken(ApprovalToken $token, string $reason): array
    {
        DB::beginTransaction();
        
        try {
            // Validate token
            $validation = $token->validate();
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['reason'],
                ];
            }
            
            $approval = $token->documentApproval;
            $requestDocument = $approval->requestDocument;
            
            // Update approval status to REJECTED
            $approval->update([
                'status' => 'REJECTED',
                'comment' => $reason,
            ]);
            
            // Update request document status to REVISE
            $requestDocument->update([
                'status' => 'REVISE',
                'revision_note' => $reason,
                'revision_deadline_at' => Carbon::now()->addDays(7), // 7 days to revise
            ]);
            
            // Mark token as used
            $token->markAsUsed();
            
            // Revoke all other pending tokens for this document
            ApprovalToken::revokeByDocument(
                $requestDocument->id,
                'Document revision requested'
            );
            
            // Create history
            DocumentHistory::create([
                'request_document_id' => $requestDocument->id,
                'actor_type' => 'email_approval',
                'actor_id' => $token->id,
                'action' => 'REVISION_REQUESTED',
                'description' => sprintf(
                    'Revision requested by %s (%s): %s',
                    $token->recipient_name ?? 'External Reviewer',
                    $approval->division,
                    $reason
                ),
                'metadata' => json_encode([
                    'division' => $approval->division,
                    'recipient_email' => $token->recipient_email,
                    'reason' => $reason,
                ]),
            ]);
            
            DB::commit();
            
            Log::info('[ApprovalService] Revision requested via token', [
                'request_document_id' => $requestDocument->id,
                'division' => $approval->division,
                'reason' => $reason,
            ]);
            
            // TODO: Send notification to supplier
            
            return [
                'success' => true,
                'message' => 'Revision requested successfully',
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[ApprovalService] Failed to request revision via token', [
                'error' => $e->getMessage(),
                'token_id' => $token->id,
            ]);
            throw $e;
        }
    }
    
    /**
     * Revoke all approval tokens for a document (e.g., when revision submitted)
     * 
     * @param RequestDocument $requestDocument
     * @param string $reason
     * @return int Number of tokens revoked
     */
    public function revokeDocumentTokens(RequestDocument $requestDocument, string $reason): int
    {
        return ApprovalToken::revokeByDocument($requestDocument->id, $reason);
    }
    
    /**
     * Get current revision number for a document
     * 
     * @param RequestDocument $requestDocument
     * @return int
     */
    protected function getCurrentRevisionNumber(RequestDocument $requestDocument): int
    {
        // Get the highest revision number from approvals
        $maxRevision = DocumentApproval::where('request_document_id', $requestDocument->id)
            ->max('revision_number');
        
        return $maxRevision ?? 1;
    }
    
    /**
     * Get approval status summary for a document
     * 
     * @param RequestDocument $requestDocument
     * @return array
     */
    public function getApprovalStatus(RequestDocument $requestDocument): array
    {
        $revisionNumber = $this->getCurrentRevisionNumber($requestDocument);
        
        $approvals = DocumentApproval::where('request_document_id', $requestDocument->id)
            ->where('revision_number', $revisionNumber)
            ->get();
        
        $status = [];
        foreach ($approvals as $approval) {
            $status[$approval->division] = [
                'status' => $approval->status,
                'approved_at' => $approval->approved_at,
                'note' => $approval->note ?? $approval->comment,
                'has_token' => $approval->activeToken()->exists(),
            ];
        }
        
        return $status;
    }
}
