<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MsaGageRr extends Model
{
    protected $table = 'msa_gage_rr';

    protected $fillable = [
        'request_document_id', 'supplier_id', 'token', 'part_no', 'part_name', 'status',
        'general', 'settings', 'measurements', 'results', 'submitted_at',
    ];

    protected $casts = [
        'general'      => 'array',
        'settings'     => 'array',
        'measurements' => 'array',
        'results'      => 'array',
        'submitted_at' => 'datetime',
    ];

    /**
     * Relasi: Belongs to RequestDocument
     */
    public function requestDocument(): BelongsTo
    {
        return $this->belongsTo(RequestDocument::class);
    }

    /**
     * Relasi: Belongs to Supplier
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Check if form is pending (not yet started)
     */
    public function isPending(): bool
    {
        return $this->status === 'PENDING' || $this->status === null;
    }

    /**
     * Check if form is in draft status (data exists but not submitted)
     */
    public function isDraft(): bool
    {
        return $this->status === 'DRAFT';
    }

    /**
     * Check if form is submitted
     */
    public function isSubmitted(): bool
    {
        return $this->status === 'SUBMITTED';
    }

    /**
     * Get status badge info for display
     * Returns: ['status' => string, 'color' => string, 'icon' => string, 'label' => string]
     */
    public function getStatusBadge(): array
    {
        $status = $this->status ?? 'PENDING';

        return match ($status) {
            'DRAFT' => [
                'status' => 'DRAFT',
                'color' => 'secondary',
                'icon' => 'ti-file-check',
                'label' => 'Draft',
            ],
            'SUBMITTED' => [
                'status' => 'SUBMITTED',
                'color' => 'success',
                'icon' => 'ti-check',
                'label' => 'Submitted',
            ],
            'PENDING' => [
                'status' => 'PENDING',
                'color' => 'warning',
                'icon' => 'ti-clock',
                'label' => 'Pending',
            ],
            default => [
                'status' => 'PENDING',
                'color' => 'warning',
                'icon' => 'ti-clock',
                'label' => 'Pending',
            ],
        };
    }
}
