<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InspectionStandard extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_document_id',
        'supplier_id',
        'model',
        'date_issued',
        'ref_no',
        'part_route',
        'part_no',
        'part_name',
        'manufacturer',
        'material',
        'general_tolerance',
        'weight',
        'revision_date',
        'revision_record',
        'status',
        'pdf_file_path',
        'form_data_json',
        'submitted_at',
        'approved_at',
        'rejected_at',
    ];

    protected $dates = [
        'date_issued',
        'revision_date',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'date_issued' => 'date',
        'revision_date' => 'date',
        'form_data_json' => 'json',
    ];

    /**
     * Relasi: Belongsto RequestDocument
     * Inspection Standard menempel pada document type IS
     */
    public function requestDocument(): BelongsTo
    {
        return $this->belongsTo(RequestDocument::class);
    }

    /**
     * Relasi: Belongsto Supplier
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Relasi: Satu IS memiliki banyak drawings
     */
    public function drawings(): HasMany
    {
        return $this->hasMany(InspectionStandardDrawing::class)
            ->orderBy('sequence_number', 'asc');
    }

    /**
     * Relasi: Satu IS memiliki banyak inspection items
     */
    public function inspectionItems(): HasMany
    {
        return $this->hasMany(InspectionStandardItem::class)
            ->orderBy('sequence_number', 'asc');
    }

    /**
     * Check if IS is in draft status
     */
    public function isDraft(): bool
    {
        return $this->status === 'DRAFT';
    }

    /**
     * Check if IS is submitted
     */
    public function isSubmitted(): bool
    {
        return in_array($this->status, ['SUBMITTED', 'PENDING_REVIEW']);
    }

    /**
     * Check if IS is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'APPROVED';
    }

    /**
     * Check if IS is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'REJECTED';
    }

    /**
     * Get status label for display
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'DRAFT' => 'Draft',
            'SUBMITTED' => 'Submitted',
            'PENDING_REVIEW' => 'Pending Review',
            'APPROVED' => 'Approved',
            'REJECTED' => 'Rejected',
            default => 'Unknown',
        };
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'DRAFT' => 'secondary',
            'SUBMITTED', 'PENDING_REVIEW' => 'warning',
            'APPROVED' => 'success',
            'REJECTED' => 'danger',
            default => 'light',
        };
    }

    /**
     * Validate if all mandatory fields are filled for submission
     */
    public function validateForSubmission(): array
    {
        $errors = [];

        // Check mandatory general information fields
        $mandatoryFields = [
            'model' => 'Model',
            'date_issued' => 'Date of Issued',
            'ref_no' => 'Ref. No',
            'part_no' => 'Part No',
            'part_name' => 'Part Name',
            'manufacturer' => 'Manufacturer',
            'material' => 'Material',
        ];

        foreach ($mandatoryFields as $field => $label) {
            if (empty($this->$field)) {
                $errors[] = "Field '{$label}' is required";
            }
        }

        // Ensure relations are loaded
        if (!$this->relationLoaded('drawings')) {
            $this->load('drawings');
        }
        if (!$this->relationLoaded('inspectionItems')) {
            $this->load('inspectionItems');
        }

        // Check if has at least one drawing
        if ($this->drawings->count() === 0) {
            $errors[] = "At least one drawing is required";
        }

        // Check if has at least one inspection item
        if ($this->inspectionItems->count() === 0) {
            $errors[] = "At least one inspection item is required";
        }

        // Validate each inspection item has mandatory fields
        foreach ($this->inspectionItems as $index => $item) {
            $itemNumber = $index + 1;
            if (empty($item->inspection_item)) {
                $errors[] = "Inspection Item #" . $itemNumber . ": Item description is required";
            }
            if (empty($item->inspection_criterion)) {
                $errors[] = "Inspection Item #" . $itemNumber . ": Inspection criterion is required";
            }
            if (empty($item->rank)) {
                $errors[] = "Inspection Item #" . $itemNumber . ": Rank is required";
            }
        }

        return $errors;
    }

    /**
     * Get display name for the record
     */
    public function getDisplayNameAttribute(): string
    {
        return "Inspection Standard - {$this->part_no} ({$this->part_name})";
    }

    /**
     * Get all form data as array for PDF generation
     */
    public function getFormDataArray(): array
    {
        // Ensure relations are loaded
        if (!$this->relationLoaded('drawings')) {
            $this->load('drawings');
        }
        if (!$this->relationLoaded('inspectionItems')) {
            $this->load('inspectionItems');
        }
        if (!$this->relationLoaded('supplier')) {
            $this->load('supplier');
        }

        return [
            'generalInformation' => [
                'model' => $this->model,
                'dateIssued' => $this->date_issued?->format('Y-m-d'),
                'refNo' => $this->ref_no,
                'partRoute' => $this->part_route,
                'partNo' => $this->part_no,
                'partName' => $this->part_name,
                'manufacturer' => $this->manufacturer,
                'material' => $this->material,
                'generalTolerance' => $this->general_tolerance,
                'weight' => $this->weight,
                'revisionDate' => $this->revision_date?->format('Y-m-d'),
                'revisionRecord' => $this->revision_record,
            ],
            'supplierInfo' => [
                'supplier' => $this->supplier?->name,
                'customer' => 'AAIJ (Purchasing & Quality Division)',
            ],
            'drawings' => $this->drawings->map(function($d) {
                // Extract revision to variable to avoid parse error
                $rev = $d->revision;
                return [
                    'drawingName' => $d->drawing_name,
                    'drawingNo' => $d->drawing_no,
                    'revision' => $rev,
                    'description' => $d->description,
                    'filePath' => $d->file_path,
                ];
            })->toArray(),
            'inspectionItems' => $this->inspectionItems->map(function($i) {
                return [
                    'inspectionItem' => $i->inspection_item,
                    'inspectionInstrument' => $i->inspection_instrument,
                    'inspectionCriterion' => $i->inspection_criterion,
                    'rank' => $i->rank,
                    'supplierSampling' => $i->supplier_sampling,
                    'aaijSampling' => $i->aaij_sampling,
                    'remarkInspectionMethod' => $i->remark_inspection_method,
                ];
            })->toArray(),
        ];
    }
}
