<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_document_id',
        'user_id',
        'employee_id',
        'scope',
        'division',
        'role',
        'stage',
        'document_type',
        'revision_number',
        'approval_cycle',
        'status',
        'note',
        'comment',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi: Approval milik satu request document
     */
    public function requestDocument()
    {
        return $this->belongsTo(RequestDocument::class);
    }

    /**
     * Relasi: Approval dilakukan oleh satu user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi: Approval dilakukan oleh satu employee (new multi-division workflow)
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Relasi: Approval has one signature
     */
    public function signature()
    {
        return $this->hasOne(ApprovalSignature::class, 'approval_id');
    }

    /**
     * Relasi: Approval has one or more tokens (for email-based approval)
     */
    public function tokens()
    {
        return $this->hasMany(ApprovalToken::class, 'document_approval_id');
    }

    /**
     * Get active (valid) token for this approval
     */
    public function activeToken()
    {
        return $this->hasOne(ApprovalToken::class, 'document_approval_id')
            ->where('is_revoked', false)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest();
    }

    /**
     * Scope query untuk approval yang masih pending
     */
    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    /**
     * Scope query untuk approval yang sudah disetujui
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'APPROVED');
    }

    /**
     * Scope query untuk approval yang ditolak
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'REJECTED');
    }

    /**
     * Check apakah approval sudah disetujui
     */
    public function isApproved(): bool
    {
        return $this->status === 'APPROVED';
    }

    /**
     * Check apakah approval masih pending
     */
    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    /**
     * Check apakah approval ditolak
     */
    public function isRejected(): bool
    {
        return $this->status === 'REJECTED';
    }
}
