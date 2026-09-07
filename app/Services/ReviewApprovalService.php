<?php

namespace App\Services;

use App\Models\DocumentApproval;
use App\Models\DocumentHistory;
use App\Models\RequestDocument;
use App\Models\RevisionConfig;
use App\Models\User;
use Carbon\Carbon;

class ReviewApprovalService
{
    /**
     * Approve dokumen dengan sistem multi-level approval
     */
    public function approveDocument(RequestDocument $requestDocument, int $userId): bool
    {
        // Validasi status harus SUBMITTED
        if ($requestDocument->status !== 'SUBMITTED') {
            throw new \Exception('Hanya dokumen dengan status SUBMITTED yang dapat di-approve');
        }

        $user = User::find($userId);
        if (!$user) {
            throw new \Exception('User tidak ditemukan');
        }

        // Get user scope
        $userScope = match ($user->role) {
            'admin_qa' => 'QA',
            'admin_purchasing' => 'PURCHASING',
            'admin_ppc' => 'PPC',
            default => null,
        };

        if (!$userScope) {
            throw new \Exception('User tidak memiliki scope yang valid untuk approval');
        }

        // Check apakah user scope termasuk dalam required scopes dokumen ini
        $requiredScopes = $requestDocument->documentType->scope ?? [];
        if (!in_array($userScope, $requiredScopes)) {
            throw new \Exception('User tidak memiliki akses untuk meng-approve dokumen ini');
        }

        // Check apakah user sudah pernah approve dengan scope yang sama
        $existingApproval = DocumentApproval::where('request_document_id', $requestDocument->id)
            ->where('scope', $userScope)
            ->where('status', 'APPROVED')
            ->first();

        if ($existingApproval) {
            throw new \Exception('Dokumen ini sudah di-approve oleh ' . $userScope);
        }

        // Buat atau update approval record
        DocumentApproval::updateOrCreate(
            [
                'request_document_id' => $requestDocument->id,
                'user_id' => $userId,
                'scope' => $userScope,
            ],
            [
                'status' => 'APPROVED',
                'approved_at' => now(),
            ]
        );

        // Catat history dengan info scope
        DocumentHistory::create([
            'request_document_id' => $requestDocument->id,
            'action' => 'APPROVE',
            'actor_id' => $userId,
            'actor_name' => $user->name,
            'note' => "Dokumen disetujui oleh {$userScope}",
        ]);

        // Check apakah semua scope sudah approve
        if ($requestDocument->allScopesApproved()) {
            // Semua scope sudah approve, update status dokumen menjadi APPROVED
            $requestDocument->update([
                'status' => 'APPROVED',
                'reviewed_by' => $userId,
                'reviewed_at' => now(),
            ]);

            // Catat history final approval
            DocumentHistory::create([
                'request_document_id' => $requestDocument->id,
                'action' => 'APPROVED_FINAL',
                'actor_id' => $userId,
                'actor_name' => 'System',
                'note' => 'Semua scope telah menyetujui. Status dokumen berubah menjadi APPROVED',
            ]);
        } else {
            // Masih ada scope yang belum approve
            $pendingScopes = $requestDocument->getPendingScopes();
            $pendingScopesText = implode(', ', $pendingScopes);
            
            $requestDocument->update([
                'reviewed_by' => $userId,
                'reviewed_at' => now(),
            ]);

            // Catat history partial approval dengan info scope yang masih pending
            DocumentHistory::create([
                'request_document_id' => $requestDocument->id,
                'action' => 'PARTIAL_APPROVE',
                'actor_id' => $userId,
                'actor_name' => $user->name,
                'note' => "Approval dari {$userScope} diterima. Menunggu approval dari: {$pendingScopesText}",
            ]);
        }

        return true;
    }

    /**
     * Reject dokumen dan request revisi
     */
    public function rejectDocument(RequestDocument $requestDocument, string $revisionNote, int $userId): bool
    {
        // Validasi status harus SUBMITTED
        if ($requestDocument->status !== 'SUBMITTED') {
            throw new \Exception('Hanya dokumen dengan status SUBMITTED yang dapat di-reject');
        }

        // Hitung deadline revisi
        $revisionDays = (int) RevisionConfig::where('key', 'revision_deadline_days')->first()?->value ?? 2;
        $revisionDeadline = now()->addDays($revisionDays);

        // Update status ke REVISE
        $requestDocument->update([
            'status' => 'REVISE',
            'reviewed_by' => $userId,
            'reviewed_at' => now(),
            'revision_note' => $revisionNote,
            'revision_deadline_at' => $revisionDeadline,
        ]);

        // Catat history
        DocumentHistory::create([
            'request_document_id' => $requestDocument->id,
            'action' => 'REJECT_REVISE',
            'actor_id' => $userId,
            'actor_name' => auth()->user()?->name ?? 'Admin',
            'note' => "Revisi diminta: {$revisionNote}",
        ]);

        // Send notification to supplier immediately
        $notificationService = app(\App\Services\NotificationService::class);
        $notificationService->notifyRevisionRequested($requestDocument);

        return true;
    }

    /**
     * Check apakah user memiliki akses untuk review dokumen ini
     */
    public function userCanReviewDocument(int $userId, RequestDocument $requestDocument): bool
    {
        $user = \App\Models\User::find($userId);
        if (!$user) {
            return false;
        }

        // Get document scopes
        $scopes = $requestDocument->documentType->scope ?? [];

        // Map user roles ke scopes
        $userScope = match ($user->role) {
            'admin_qa' => 'QA',
            'admin_purchasing' => 'PURCHASING',
            'admin_ppc' => 'PPC',
            default => null,
        };

        if (!$userScope) {
            return false;
        }

        return in_array($userScope, $scopes);
    }

    /**
     * Get documents yang perlu di-review oleh user (SUBMITTED)
     * Hanya tampilkan dokumen yang belum di-approve oleh scope user ini
     */
    public function getPendingDocumentsForReview(int $userId)
    {
        $user = \App\Models\User::find($userId);
        if (!$user || !$user->isInternalAdmin()) {
            return collect();
        }

        // Map user role ke scope
        $scope = match ($user->role) {
            'admin_qa' => 'QA',
            'admin_purchasing' => 'PURCHASING',
            'admin_ppc' => 'PPC',
            default => null,
        };

        if (!$scope) {
            return collect();
        }

        return RequestDocument::where('status', 'SUBMITTED')
            ->whereHas('documentType', function ($q) use ($scope) {
                $q->whereJsonContains('scope', $scope);
            })
            // Exclude documents yang sudah di-approve oleh scope ini
            ->whereDoesntHave('approvals', function ($q) use ($scope) {
                $q->where('scope', $scope)
                  ->where('status', 'APPROVED');
            })
            ->with(['documentRequest.supplier', 'documentType', 'histories', 'approvals.user'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get documents yang perlu revisi (REVISE) yang sesuai scope user
     */
    public function getRevisionDocumentsForReview(int $userId)
    {
        $user = \App\Models\User::find($userId);
        if (!$user || !$user->isInternalAdmin()) {
            return collect();
        }

        // Map user role ke scope
        $scope = match ($user->role) {
            'admin_qa' => 'QA',
            'admin_purchasing' => 'PURCHASING',
            'admin_ppc' => 'PPC',
            default => null,
        };

        if (!$scope) {
            return collect();
        }

        return RequestDocument::where('status', 'REVISE')
            ->whereHas('documentType', function ($q) use ($scope) {
                $q->whereJsonContains('scope', $scope);
            })
            ->with(['documentRequest.supplier', 'documentType', 'histories'])
            ->orderByDesc('updated_at')
            ->get();
    }

    /**
     * Get documents yang perlu di-review oleh user (deprecated - gunakan getPendingDocumentsForReview)
     */
    public function getDocumentsForReview(int $userId)
    {
        return $this->getPendingDocumentsForReview($userId);
    }

    /**
     * Get semua dokumen dalam status REVISE yang deadline-nya dekat atau sudah lewat
     */
    public function getRevisionDocumentsNearingDeadline()
    {
        $reminderDays = (int) RevisionConfig::where('key', 'reminder_days_before')->first()?->value ?? 2;

        return RequestDocument::where('status', 'REVISE')
            ->whereBetween('revision_deadline_at', [
                now(),
                now()->addDays($reminderDays),
            ])
            ->with(['documentRequest.supplier', 'documentType', 'histories'])
            ->get();
    }

    /**
     * Check apakah semua dokumen mandatory sudah APPROVED
     */
    public function allMandatoryDocumentsApproved(\App\Models\DocumentRequest $request): bool
    {
        $mandatoryDocs = $request->requestDocuments()
            ->whereHas('documentType', function ($q) {
                $q->where('is_mandatory', true);
            })
            ->get();

        foreach ($mandatoryDocs as $doc) {
            if ($doc->status !== 'APPROVED') {
                return false;
            }
        }

        return true;
    }

    /**
     * Get dokumen APPROVED untuk user (sesuai scope)
     * Tampilkan dokumen yang sudah final APPROVED ATAU yang sudah di-approve oleh scope user ini
     */
    public function getApprovedDocumentsForReview(int $userId)
    {
        $user = \App\Models\User::find($userId);
        if (!$user || !$user->isInternalAdmin()) {
            return collect();
        }

        // Map user role ke scope
        $scope = match ($user->role) {
            'admin_qa' => 'QA',
            'admin_purchasing' => 'PURCHASING',
            'admin_ppc' => 'PPC',
            default => null,
        };

        if (!$scope) {
            return collect();
        }

        // Get dokumen yang sudah final APPROVED
        $finalApproved = RequestDocument::where('status', 'APPROVED')
            ->whereHas('documentType', function ($q) use ($scope) {
                $q->whereJsonContains('scope', $scope);
            })
            ->with(['documentRequest.supplier', 'documentType', 'reviewedBy', 'approvals.user'])
            ->orderByDesc('reviewed_at')
            ->get();

        // Get dokumen SUBMITTED yang sudah di-approve oleh scope ini (tapi belum final approved)
        $partialApproved = RequestDocument::where('status', 'SUBMITTED')
            ->whereHas('documentType', function ($q) use ($scope) {
                $q->whereJsonContains('scope', $scope);
            })
            ->whereHas('approvals', function ($q) use ($scope) {
                $q->where('scope', $scope)
                  ->where('status', 'APPROVED');
            })
            ->with(['documentRequest.supplier', 'documentType', 'reviewedBy', 'approvals.user'])
            ->orderByDesc('reviewed_at')
            ->get();

        return $finalApproved->merge($partialApproved)->sortByDesc('reviewed_at')->values();
    }
}
