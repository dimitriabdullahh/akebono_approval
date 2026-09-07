<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionStandardItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_standard_id',
        'inspection_item',
        'inspection_instrument',
        'inspection_criterion',
        'rank',
        'supplier_sampling',
        'aaij_sampling',
        'remark_inspection_method',
        'sequence_number',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * Relasi: Belongs to InspectionStandard
     */
    public function inspectionStandard(): BelongsTo
    {
        return $this->belongsTo(InspectionStandard::class);
    }

    /**
     * Get row number for display (1-indexed)
     */
    public function getRowNumberAttribute(): int
    {
        return $this->sequence_number + 1;
    }

    /**
     * Validate required fields
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->inspection_item)) {
            $errors[] = 'Inspection Item is required';
        }

        if (empty($this->inspection_criterion)) {
            $errors[] = 'Inspection Criterion is required';
        }

        if (empty($this->rank)) {
            $errors[] = 'Rank is required';
        }

        return $errors;
    }

    /**
     * Check if all mandatory fields are filled
     */
    public function isFilled(): bool
    {
        return !empty($this->inspection_item)
            && !empty($this->inspection_criterion)
            && !empty($this->rank);
    }
}
