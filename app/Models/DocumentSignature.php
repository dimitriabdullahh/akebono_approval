<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentSignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_document_id',
        'role_type',
        'employee_id',
        'npk',
        'name',
        'role_label',
        'notes',
        'signed_at',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    /**
     * Get the request document that owns this signature
     */
    public function requestDocument(): BelongsTo
    {
        return $this->belongsTo(RequestDocument::class);
    }

    /**
     * Get the employee who created this signature
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Scope: Get signature by role type
     */
    public function scopeByRole($query, string $roleType)
    {
        return $query->where('role_type', $roleType);
    }

    /**
     * Scope: Get checker signatures
     */
    public function scopeChecker($query)
    {
        return $query->where('role_type', 'checker');
    }

    /**
     * Scope: Get approver signatures
     */
    public function scopeApprover($query)
    {
        return $query->where('role_type', 'approver');
    }

    /**
     * Scope: Get receiver signatures
     */
    public function scopeReceiver($query)
    {
        return $query->where('role_type', 'receiver');
    }

    /**
     * Check if signature is by checker
     */
    public function isChecker(): bool
    {
        return $this->role_type === 'checker';
    }

    /**
     * Check if signature is by approver
     */
    public function isApprover(): bool
    {
        return $this->role_type === 'approver';
    }

    /**
     * Check if signature is by receiver
     */
    public function isReceiver(): bool
    {
        return $this->role_type === 'receiver';
    }

    /**
     * Get formatted signed date
     */
    public function getFormattedSignedDateAttribute(): string
    {
        return $this->signed_at ? $this->signed_at->format('d/m/Y') : '';
    }

    /**
     * Get formatted signed datetime
     */
    public function getFormattedSignedDatetimeAttribute(): string
    {
        return $this->signed_at ? $this->signed_at->format('d/m/Y H:i') : '';
    }
}
