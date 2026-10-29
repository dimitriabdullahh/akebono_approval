<?php

namespace App\Services;

use App\Models\RequestDocument;
use App\Models\DocumentApproval;
use App\Models\DocumentHistory;
use App\Models\Employee;
use App\Models\InspectionStandard;
use App\Models\PackagingStandard;
use App\Models\PartSubmissionWarrant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovalWorkflowService
{
    /**
     * Document type constants
     */
    const DOC_TYPE_IS = 'IS';
    const DOC_TYPE_PS = 'PS';
    const DOC_TYPE_PSW = 'PSW';

    /**
     * IS Stage constants (Sequential)
     */
    const STAGE_RECEIVED = 'RECEIVED';
    const STAGE_CHECK = 'CHECK';
    const STAGE_APPROVE = 'APPROVE';

    /**
     * Status constants
     */
    const STATUS_PENDING = 'PENDING';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_REVISION_REQUESTED = 'REVISION_REQUESTED';

    /**
     * Record RECEIVED action for IS document (sequential workflow)
     * Any admin can receive, but only first receive counts
     * 
     * @param RequestDocument $document
     * @param Employee $employee
     * @param string|null $comment
     * @return DocumentApproval
     */
    public function recordReceived(RequestDocument $document, Employee $employee, ?string $comment = null): DocumentApproval
    {
        DB::beginTransaction();
        
        try {
            // Check if already received
            $existingReceived = DocumentApproval::where('request_document_id', $document->id)
                ->where('stage', self::STAGE_RECEIVED)
                ->where('revision_number', $this->getCurrentRevision($document))
                ->first();

            if ($existingReceived) {
                throw new \Exception('Document already received for current revision');
            }

            // Create received approval record
            $approval = DocumentApproval::create([
                'request_document_id' => $document->id,
                'employee_id' => $employee->id,
                'division' => $employee->division,
                'role' => 'Received', // Special role for IS received stage
                'stage' => self::STAGE_RECEIVED,
                'document_type' => self::DOC_TYPE_IS,
                'revision_number' => $this->getCurrentRevision($document),
                'approval_cycle' => $this->getCurrentCycle($document),
                'status' => self::STATUS_APPROVED,
                'comment' => $comment,
                'approved_at' => now(),
            ]);

            // Create history record
            DocumentHistory::create([
                'request_document_id' => $document->id,
                'action' => 'RECEIVED',
                'actor_id' => $employee->id,
                'actor_name' => $employee->name . ' (' . $employee->npk . ')',
                'note' => $comment ?? 'Document received for checking',
            ]);

            // Update document status
            $document->update(['status' => 'RECEIVED']);

            DB::commit();
            
            Log::info('IS Document received', [
                'document_id' => $document->id,
                'employee' => $employee->name,
                'npk' => $employee->npk,
            ]);

            return $approval;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error recording received', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Check document (Checker role)
     * For IS: QA Checker only
     * For PS/PSW: Division-specific Checker
     * 
     * @param RequestDocument $document
     * @param Employee $employee
     * @param string|null $comment
     * @return DocumentApproval
     */
    public function check(RequestDocument $document, Employee $employee, ?string $comment = null): DocumentApproval
    {
        DB::beginTransaction();
        
        try {
            // Get document type
            $docType = $this->getDocumentType($document);
            
            // Validate employee is Checker
            if (!$employee->isChecker()) {
                throw new \Exception('Only Checkers can perform check action');
            }

            // For IS: Only QA Checker
            if ($docType === self::DOC_TYPE_IS && !$employee->isQA()) {
                throw new \Exception('IS documents can only be checked by QA Checker');
            }

            // Check if already checked by this division
            $existingCheck = DocumentApproval::where('request_document_id', $document->id)
                ->where('division', $employee->division)
                ->where('role', Employee::ROLE_CHECKER)
                ->where('revision_number', $this->getCurrentRevision($document))
                ->where('status', self::STATUS_APPROVED)
                ->first();

            if ($existingCheck) {
                throw new \Exception('Document already checked by ' . $employee->division . ' Checker');
            }

            // For IS: Ensure document was received first
            if ($docType === self::DOC_TYPE_IS) {
                $received = DocumentApproval::where('request_document_id', $document->id)
                    ->where('stage', self::STAGE_RECEIVED)
                    ->where('revision_number', $this->getCurrentRevision($document))
                    ->exists();

                if (!$received) {
                    throw new \Exception('Document must be received before checking');
                }
            }

            // Create check approval record
            $approval = DocumentApproval::create([
                'request_document_id' => $document->id,
                'employee_id' => $employee->id,
                'division' => $employee->division,
                'role' => Employee::ROLE_CHECKER,
                'stage' => $docType === self::DOC_TYPE_IS ? self::STAGE_CHECK : null,
                'document_type' => $docType,
                'revision_number' => $this->getCurrentRevision($document),
                'approval_cycle' => $this->getCurrentCycle($document),
                'status' => self::STATUS_APPROVED,
                'comment' => $comment,
                'approved_at' => now(),
            ]);

            // Create history record
            DocumentHistory::create([
                'request_document_id' => $document->id,
                'action' => 'CHECKED',
                'actor_id' => $employee->id,
                'actor_name' => $employee->name . ' (' . $employee->npk . ')',
                'note' => $comment ?? 'Document checked by ' . $employee->division . ' Checker',
            ]);

            // Update document status based on type
            if ($docType === self::DOC_TYPE_IS) {
                $document->update(['status' => 'CHECK_OK']);
            } else {
                // For PS/PSW: Check if all divisions checked
                $this->updateParallelStatus($document);
            }

            DB::commit();
            
            Log::info('Document checked', [
                'document_id' => $document->id,
                'document_type' => $docType,
                'division' => $employee->division,
                'employee' => $employee->name,
            ]);

            return $approval;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error checking document', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Approve document (Approver role)
     * For IS: QA Approver only (after check)
     * For PS/PSW: Division-specific Approver (after division check)
     * 
     * @param RequestDocument $document
     * @param Employee $employee
     * @param string|null $comment
     * @return DocumentApproval
     */
    public function approve(RequestDocument $document, Employee $employee, ?string $comment = null): DocumentApproval
    {
        DB::beginTransaction();
        
        try {
            // Get document type
            $docType = $this->getDocumentType($document);
            
            // Validate employee is Approver
            if (!$employee->isApprover()) {
                throw new \Exception('Only Approvers can perform approve action');
            }

            // For IS: Only QA Approver
            if ($docType === self::DOC_TYPE_IS && !$employee->isQA()) {
                throw new \Exception('IS documents can only be approved by QA Approver');
            }

            // Check if already approved by this division
            $existingApproval = DocumentApproval::where('request_document_id', $document->id)
                ->where('division', $employee->division)
                ->where('role', Employee::ROLE_APPROVER)
                ->where('revision_number', $this->getCurrentRevision($document))
                ->where('status', self::STATUS_APPROVED)
                ->first();

            if ($existingApproval) {
                throw new \Exception('Document already approved by ' . $employee->division . ' Approver');
            }

            // Ensure division Checker completed first
            $checked = DocumentApproval::where('request_document_id', $document->id)
                ->where('division', $employee->division)
                ->where('role', Employee::ROLE_CHECKER)
                ->where('revision_number', $this->getCurrentRevision($document))
                ->where('status', self::STATUS_APPROVED)
                ->exists();

            if (!$checked) {
                throw new \Exception($employee->division . ' Checker must complete checking before approval');
            }

            // Create approval record
            $approval = DocumentApproval::create([
                'request_document_id' => $document->id,
                'employee_id' => $employee->id,
                'division' => $employee->division,
                'role' => Employee::ROLE_APPROVER,
                'stage' => $docType === self::DOC_TYPE_IS ? self::STAGE_APPROVE : null,
                'document_type' => $docType,
                'revision_number' => $this->getCurrentRevision($document),
                'approval_cycle' => $this->getCurrentCycle($document),
                'status' => self::STATUS_APPROVED,
                'comment' => $comment,
                'approved_at' => now(),
            ]);

            // Create history record
            DocumentHistory::create([
                'request_document_id' => $document->id,
                'action' => 'APPROVED',
                'actor_id' => $employee->id,
                'actor_name' => $employee->name . ' (' . $employee->npk . ')',
                'note' => $comment ?? 'Document approved by ' . $employee->division . ' Approver',
            ]);

            // Check if final approval complete
            $this->checkFinalApproval($document, $docType);

            DB::commit();
            
            Log::info('Document approved', [
                'document_id' => $document->id,
                'document_type' => $docType,
                'division' => $employee->division,
                'employee' => $employee->name,
            ]);

            return $approval;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving document', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Request revision (Checker or Approver can request)
     * 
     * @param RequestDocument $document
     * @param Employee $employee
     * @param string $reason
     * @return DocumentApproval
     */
    public function requestRevision(RequestDocument $document, Employee $employee, string $reason): DocumentApproval
    {
        DB::beginTransaction();
        
        try {
            $docType = $this->getDocumentType($document);

            // Create revision request record
            $approval = DocumentApproval::create([
                'request_document_id' => $document->id,
                'employee_id' => $employee->id,
                'division' => $employee->division,
                'role' => $employee->role,
                'document_type' => $docType,
                'revision_number' => $this->getCurrentRevision($document),
                'approval_cycle' => $this->getCurrentCycle($document),
                'status' => self::STATUS_REVISION_REQUESTED,
                'comment' => $reason,
                'approved_at' => now(),
            ]);

            // Create history record
            DocumentHistory::create([
                'request_document_id' => $document->id,
                'action' => 'REVISION_REQUESTED',
                'actor_id' => $employee->id,
                'actor_name' => $employee->name . ' (' . $employee->npk . ')',
                'note' => 'Revision requested: ' . $reason,
            ]);

            // Update document status
            $document->update([
                'status' => 'REVISE',
                'revision_note' => $reason,
            ]);

            DB::commit();
            
            Log::info('Revision requested', [
                'document_id' => $document->id,
                'employee' => $employee->name,
                'division' => $employee->division,
                'reason' => $reason,
            ]);

            return $approval;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error requesting revision', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Check if all required approvals are complete and update to FINAL_APPROVED
     * 
     * @param RequestDocument $document
     * @param string $docType
     * @return void
     */
    protected function checkFinalApproval(RequestDocument $document, string $docType): void
    {
        $revision = $this->getCurrentRevision($document);

        if ($docType === self::DOC_TYPE_IS) {
            // IS: Sequential - just need QA Approver
            $qaApproved = DocumentApproval::where('request_document_id', $document->id)
                ->where('division', Employee::DIVISION_QA)
                ->where('role', Employee::ROLE_APPROVER)
                ->where('revision_number', $revision)
                ->where('status', self::STATUS_APPROVED)
                ->exists();

            if ($qaApproved) {
                $document->update(['status' => 'APPROVED']);
                
                DocumentHistory::create([
                    'request_document_id' => $document->id,
                    'action' => 'FINAL_APPROVED',
                    'actor_id' => null,
                    'actor_name' => 'System',
                    'note' => 'All required approvals completed',
                ]);
            }
        } else {
            // PS/PSW: Parallel - need all three divisions
            $qaApproved = DocumentApproval::where('request_document_id', $document->id)
                ->where('division', Employee::DIVISION_QA)
                ->where('role', Employee::ROLE_APPROVER)
                ->where('revision_number', $revision)
                ->where('status', self::STATUS_APPROVED)
                ->exists();

            $pccApproved = DocumentApproval::where('request_document_id', $document->id)
                ->where('division', Employee::DIVISION_PCC)
                ->where('role', Employee::ROLE_APPROVER)
                ->where('revision_number', $revision)
                ->where('status', self::STATUS_APPROVED)
                ->exists();

            $purchasingApproved = DocumentApproval::where('request_document_id', $document->id)
                ->where('division', Employee::DIVISION_PURCHASING)
                ->where('role', Employee::ROLE_APPROVER)
                ->where('revision_number', $revision)
                ->where('status', self::STATUS_APPROVED)
                ->exists();

            if ($qaApproved && $pccApproved && $purchasingApproved) {
                $document->update(['status' => 'APPROVED']);
                
                DocumentHistory::create([
                    'request_document_id' => $document->id,
                    'action' => 'FINAL_APPROVED',
                    'actor_id' => null,
                    'actor_name' => 'System',
                    'note' => 'All divisions approved (QA, PCC, Purchasing)',
                ]);
            } else {
                // Update to in-progress status
                $document->update(['status' => 'APPROVAL_IN_PROGRESS']);
            }
        }
    }

    /**
     * Update status for parallel approval (PS/PSW)
     * 
     * @param RequestDocument $document
     * @return void
     */
    protected function updateParallelStatus(RequestDocument $document): void
    {
        $document->update(['status' => 'APPROVAL_IN_PROGRESS']);
    }

    /**
     * Get document type from RequestDocument
     * 
     * @param RequestDocument $document
     * @return string
     */
    protected function getDocumentType(RequestDocument $document): string
    {
        // Check which related model exists
        if ($document->inspectionStandard()->exists()) {
            return self::DOC_TYPE_IS;
        } elseif ($document->packagingStandard()->exists()) {
            return self::DOC_TYPE_PS;
        } elseif ($document->partSubmissionWarrant()->exists()) {
            return self::DOC_TYPE_PSW;
        }

        // Fallback: check document type code
        $documentType = $document->documentType;
        if ($documentType) {
            if (stripos($documentType->code, 'INSPECTION') !== false) {
                return self::DOC_TYPE_IS;
            } elseif (stripos($documentType->code, 'PACKAGING') !== false) {
                return self::DOC_TYPE_PS;
            } elseif (stripos($documentType->code, 'PSW') !== false || stripos($documentType->code, 'WARRANT') !== false) {
                return self::DOC_TYPE_PSW;
            }
        }

        throw new \Exception('Unable to determine document type');
    }

    /**
     * Get current revision number for document
     * 
     * @param RequestDocument $document
     * @return int
     */
    protected function getCurrentRevision(RequestDocument $document): int
    {
        // Get highest revision number from approvals
        $maxRevision = DocumentApproval::where('request_document_id', $document->id)
            ->max('revision_number');

        return $maxRevision ?? 1;
    }

    /**
     * Get current approval cycle for document
     * 
     * @param RequestDocument $document
     * @return int
     */
    protected function getCurrentCycle(RequestDocument $document): int
    {
        // Get highest cycle number from approvals
        $maxCycle = DocumentApproval::where('request_document_id', $document->id)
            ->max('approval_cycle');

        return $maxCycle ?? 1;
    }

    /**
     * Get approval progress for document
     * 
     * @param RequestDocument $document
     * @return array
     */
    public function getApprovalProgress(RequestDocument $document): array
    {
        $docType = $this->getDocumentType($document);
        $revision = $this->getCurrentRevision($document);

        $progress = [
            'document_type' => $docType,
            'revision' => $revision,
            'status' => $document->status,
        ];

        if ($docType === self::DOC_TYPE_IS) {
            // IS Sequential Progress
            $progress['stages'] = [
                'received' => DocumentApproval::where('request_document_id', $document->id)
                    ->where('stage', self::STAGE_RECEIVED)
                    ->where('revision_number', $revision)
                    ->exists(),
                'checked' => DocumentApproval::where('request_document_id', $document->id)
                    ->where('division', Employee::DIVISION_QA)
                    ->where('role', Employee::ROLE_CHECKER)
                    ->where('revision_number', $revision)
                    ->where('status', self::STATUS_APPROVED)
                    ->exists(),
                'approved' => DocumentApproval::where('request_document_id', $document->id)
                    ->where('division', Employee::DIVISION_QA)
                    ->where('role', Employee::ROLE_APPROVER)
                    ->where('revision_number', $revision)
                    ->where('status', self::STATUS_APPROVED)
                    ->exists(),
            ];
        } else {
            // PS/PSW Parallel Progress
            $progress['divisions'] = [];
            
            foreach ([Employee::DIVISION_QA, Employee::DIVISION_PCC, Employee::DIVISION_PURCHASING] as $division) {
                $progress['divisions'][$division] = [
                    'checked' => DocumentApproval::where('request_document_id', $document->id)
                        ->where('division', $division)
                        ->where('role', Employee::ROLE_CHECKER)
                        ->where('revision_number', $revision)
                        ->where('status', self::STATUS_APPROVED)
                        ->exists(),
                    'approved' => DocumentApproval::where('request_document_id', $document->id)
                        ->where('division', $division)
                        ->where('role', Employee::ROLE_APPROVER)
                        ->where('revision_number', $revision)
                        ->where('status', self::STATUS_APPROVED)
                        ->exists(),
                ];
            }
        }

        return $progress;
    }
}
