<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_request_id',
        'request_part_id',
        'document_type_id',
        'status',
        'file_path',
        'original_filename',
        'notes',
        'uploaded_at',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'revision_note',
        'revision_deadline_at',
        'revised_by_team',
        'revised_by_user_id',
        'revised_at',
        'revision_count',
        'version',
        'pdf_path',
        'checked_by_employee_id',
        'checked_at',
        'checker_notes',
        'checker_esign_token',
        'received_by_employee_id',
        'received_at',
        'receiver_esign_token',
        'workflow_stage',
    ];

    protected $dates = [
        'uploaded_at',
        'submitted_at',
        'reviewed_at',
        'revision_deadline_at',
        'revised_at',
        'checked_at',
        'received_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Relasi: Banyak request documents milik satu document request
     */
    public function documentRequest()
    {
        return $this->belongsTo(DocumentRequest::class);
    }

    /**
     * Relasi: Banyak request documents milik satu request part
     */
    public function requestPart()
    {
        return $this->belongsTo(RequestPart::class);
    }

    /**
     * Relasi: Banyak request documents milik satu document type
     */
    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }

    /**
     * Relasi: User reviewer
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Alias untuk reviewer relationship
     */
    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Relasi: Satu request document memiliki banyak histories
     */
    public function histories()
    {
        return $this->hasMany(DocumentHistory::class);
    }

    /**
     * Relasi: Satu request document memiliki banyak approvals
     */
    public function approvals()
    {
        return $this->hasMany(DocumentApproval::class);
    }

    /**
     * Relasi: Inspection Standard (jika document type adalah IS)
     */
    public function inspectionStandard()
    {
        return $this->hasOne(InspectionStandard::class);
    }

    /**
     * Relasi: Packaging Standard (jika document type adalah PACKAGING_STANDARD)
     */
    public function packagingStandard()
    {
        return $this->hasOne(PackagingStandard::class);
    }

    /**
     * Relasi: Part Submission Warrant (jika document type adalah PSW)
     */
    public function partSubmissionWarrant()
    {
        return $this->hasOne(PartSubmissionWarrant::class);
    }

    /**
     * Relasi: MSA Gage R&R (jika document type adalah MSA)
     */
    public function msaGageRr()
    {
        return $this->hasOne(MsaGageRr::class);
    }

    /**
     * Relasi: Employee yang melakukan checking
     */
    public function checkedByEmployee()
    {
        return $this->belongsTo(Employee::class, 'checked_by_employee_id');
    }

    /**
     * Relasi: Employee yang melakukan receiving (untuk IS)
     */
    public function receivedByEmployee()
    {
        return $this->belongsTo(Employee::class, 'received_by_employee_id');
    }

    /**
     * Relasi: Checker signatures
     */
    public function checkerSignatures()
    {
        return $this->hasMany(CheckerSignature::class);
    }

    /**
     * Get checker signature
     */
    public function checkerSignature()
    {
        return $this->hasOne(CheckerSignature::class)
            ->where('signature_type', CheckerSignature::TYPE_CHECKER);
    }

    /**
     * Get receiver signature
     */
    public function receiverSignature()
    {
        return $this->hasOne(CheckerSignature::class)
            ->where('signature_type', CheckerSignature::TYPE_RECEIVER);
    }

    /**
     * Relasi: Document signatures (QR code based)
     */
    public function signatures()
    {
        return $this->hasMany(DocumentSignature::class);
    }

    /**
     * Get checker e-signature (QR code)
     */
    public function checkerEsignature()
    {
        return $this->hasOne(DocumentSignature::class)
            ->where('role_type', 'checker');
    }

    /**
     * Get approver e-signature (QR code)
     */
    public function approverEsignature()
    {
        return $this->hasOne(DocumentSignature::class)
            ->where('role_type', 'approver');
    }

    /**
     * Get receiver e-signature (QR code)
     */
    public function receiverEsignature()
    {
        return $this->hasOne(DocumentSignature::class)
            ->where('role_type', 'receiver');
    }

    /**
     * Get pending approvals untuk dokumen ini
     */
    public function pendingApprovals()
    {
        return $this->hasMany(DocumentApproval::class)->where('status', 'PENDING');
    }

    /**
     * Get approved approvals untuk dokumen ini
     */
    public function approvedApprovals()
    {
        return $this->hasMany(DocumentApproval::class)->where('status', 'APPROVED');
    }

    /**
     * Check apakah semua scope sudah approve
     */
    public function allScopesApproved(): bool
    {
        $requiredScopes = $this->documentType->scope ?? [];
        
        // Ensure scope is array (handle cases where cast might not apply)
        if (is_string($requiredScopes)) {
            $requiredScopes = json_decode($requiredScopes, true) ?? [];
        }
        
        if (empty($requiredScopes)) {
            return false;
        }

        $approvedScopes = $this->approvals()
            ->where('status', 'APPROVED')
            ->pluck('scope')
            ->toArray();

        // Check apakah semua required scopes sudah ada di approved scopes
        foreach ($requiredScopes as $scope) {
            if (!in_array($scope, $approvedScopes)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get scopes yang masih pending approval
     */
    public function getPendingScopes(): array
    {
        $requiredScopes = $this->documentType->scope ?? [];
        
        // Ensure scope is array (handle cases where cast might not apply)
        if (is_string($requiredScopes)) {
            $requiredScopes = json_decode($requiredScopes, true) ?? [];
        }
        
        $approvedScopes = $this->approvals()
            ->where('status', 'APPROVED')
            ->pluck('scope')
            ->toArray();

        return array_values(array_diff($requiredScopes, $approvedScopes));
    }

    /**
     * Get scopes yang sudah approved
     */
    public function getApprovedScopes(): array
    {
        return $this->approvals()
            ->where('status', 'APPROVED')
            ->pluck('scope')
            ->toArray();
    }

    /**
     * Check apakah deadline revisi sudah terlampaui
     */
    public function getIsDeadlinePassedAttribute()
    {
        if (!$this->revision_deadline_at) {
            return false;
        }
        return now()->isAfter($this->revision_deadline_at);
    }

    /**
     * Sisa waktu deadline (dalam format readable)
     */
    public function getRemainingDeadlineAttribute()
    {
        if (!$this->revision_deadline_at) {
            return null;
        }
        return $this->revision_deadline_at->diffForHumans();
    }

    // ===== REVISION TRACKING RELATIONSHIPS & METHODS =====

    /**
     * Relasi: User yang memberikan revisi
     */
    public function revisedByUser()
    {
        return $this->belongsTo(User::class, 'revised_by_user_id');
    }

    /**
     * Relasi: Riwayat revisi dokumen ini
     */
    public function revisions()
    {
        return $this->hasMany(DocumentRevision::class)->orderBy('revised_at', 'desc');
    }

    /**
     * Check apakah dokumen sedang dalam status revisi
     */
    public function isRevision(): bool
    {
        return $this->status === 'REVISE';
    }

    /**
     * Check apakah dokumen bisa direvisi oleh supplier
     */
    public function canBeRevised(): bool
    {
        return $this->status === 'REVISE' && !empty($this->revised_by_team);
    }

    /**
     * Get informasi revisi terbaru
     */
    public function getRevisionInfo(): array
    {
        if (!$this->isRevision()) {
            return [];
        }

        return [
            'team' => $this->revised_by_team,
            'team_label' => $this->getTeamLabel(),
            'note' => $this->revision_note,
            'revised_at' => $this->revised_at,
            'count' => $this->revision_count ?? 0,
            'version' => $this->version ?? 1,
        ];
    }

    /**
     * Get label team yang memberikan revisi
     */
    public function getTeamLabel(): string
    {
        return match($this->revised_by_team) {
            'qa' => 'Quality Assurance',
            'purchasing' => 'Purchasing',
            'ppc' => 'Production Planning & Control',
            default => ucfirst($this->revised_by_team ?? 'Unknown'),
        };
    }

    /**
     * Simpan revisi ke riwayat dan update counter
     */
    public function recordRevision(string $team, ?int $userId, string $note): void
    {
        // Catat ke riwayat
        $this->revisions()->create([
            'team' => $team,
            'user_id' => $userId,
            'note' => $note,
            'revised_at' => now(),
        ]);

        // Update dokumen
        $this->update([
            'status' => 'REVISE',
            'revised_by_team' => $team,
            'revised_by_user_id' => $userId,
            'revised_at' => now(),
            'revision_note' => $note,
            'revision_count' => ($this->revision_count ?? 0) + 1,
        ]);
    }

    /**
     * Supplier resubmit dokumen setelah revisi
     */
    public function resubmit(?string $pdfPath = null): void
    {
        // Update riwayat revisi terakhir dengan timestamp resubmit
        $lastRevision = $this->revisions()->whereNull('resubmitted_at')->first();
        if ($lastRevision) {
            $lastRevision->update(['resubmitted_at' => now()]);
        }

        // Increment version and revision count
        $newVersion = ($this->version ?? 1) + 1;
        $revisionCount = ($this->revision_count ?? 0) + 1;

        // Prepare update data
        $updateData = [
            'status' => 'SUBMITTED',
            'submitted_at' => now(),
            'version' => $newVersion,
            'revision_count' => $revisionCount,
            // Clear revision fields
            'revised_by_team' => null,
            'revised_by_user_id' => null,
            'revised_at' => null,
            'revision_note' => null,
        ];

        // Add PDF path if provided
        if ($pdfPath) {
            $updateData['file_path'] = $pdfPath;
            $updateData['pdf_path'] = $pdfPath;
        }

        // Update dokumen kembali ke SUBMITTED
        $this->update($updateData);
    }

    /**
     * Get PDF URL dengan cache busting
     */
    public function getPdfUrl(): ?string
    {
        if (!$this->pdf_path) {
            return null;
        }

        // Add version parameter untuk cache busting
        $timestamp = $this->updated_at ? $this->updated_at->timestamp : time();
        return $this->pdf_path . '?v=' . $timestamp;
    }
}
