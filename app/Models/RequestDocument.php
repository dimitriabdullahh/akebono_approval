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
    ];

    protected $dates = [
        'uploaded_at',
        'submitted_at',
        'reviewed_at',
        'revision_deadline_at',
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
        
        $approvedScopes = $this->approvals()
            ->where('status', 'APPROVED')
            ->pluck('scope')
            ->toArray();

        return array_diff($requiredScopes, $approvedScopes);
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
}
