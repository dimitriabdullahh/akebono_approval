<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackagingStandard extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_document_id',
        'supplier_id',
        // Step 1: Informasi Umum
        'document_no',
        'revision',
        'date',
        'customer',
        'part_no',
        'part_name',
        'supplier_name',
        'qty',
        // Step 2: Spesifikasi Kemasan
        'packaging_type',
        'packaging_size_mm',
        'packaging_lot_pcs_box',
        'net_weight_g_pc',
        'total_weight_kg_box',
        // Step 3: Visualisasi & Approval
        'image_photographic',
        'image_part_identification',
        'image_visualisasi_packing',
        'image_label_packing',
        'revision_history',
        'prepared_by_name',
        'prepared_by_date',
        'checked_by_name',
        'checked_by_date',
        'approved_by_name',
        'approved_by_date',
        // Status & metadata
        'status',
        'pdf_file_path',
        'form_data_json',
        'submitted_at',
        'approved_at',
        'rejected_at',
    ];

    protected $dates = [
        'date',
        'prepared_by_date',
        'checked_by_date',
        'approved_by_date',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'date' => 'date',
        'prepared_by_date' => 'date',
        'checked_by_date' => 'date',
        'approved_by_date' => 'date',
        'qty' => 'integer',
        'packaging_lot_pcs_box' => 'integer',
        'net_weight_g_pc' => 'decimal:2',
        'total_weight_kg_box' => 'decimal:2',
        'revision_history' => 'json',
        'form_data_json' => 'json',
    ];

    /**
     * Relasi: Belongs to RequestDocument
     * Packaging Standard attached to PACKAGING_STANDARD document type
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
     * Check if PS is in draft status
     */
    public function isDraft(): bool
    {
        return $this->status === 'DRAFT';
    }

    /**
     * Check if PS is submitted
     */
    public function isSubmitted(): bool
    {
        return in_array($this->status, ['SUBMITTED', 'PENDING_REVIEW']);
    }

    /**
     * Check if PS is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'APPROVED';
    }

    /**
     * Check if PS is rejected
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

        // Step 1: Informasi Umum - mandatory fields
        $mandatoryFields = [
            'document_no' => 'Document No.',
            'revision' => 'Revision',
            'date' => 'Date',
            'part_no' => 'Part No.',
            'part_name' => 'Part Name',
            'qty' => 'Qty',
        ];

        foreach ($mandatoryFields as $field => $label) {
            if (empty($this->$field)) {
                $errors[] = "Field '{$label}' is required";
            }
        }

        // Step 2: Spesifikasi Kemasan - mandatory fields
        $specsFields = [
            'packaging_type' => 'Packaging Type',
            'packaging_size_mm' => 'Packaging Size (mm)',
            'packaging_lot_pcs_box' => 'Packaging Lot (pcs/box)',
        ];

        foreach ($specsFields as $field => $label) {
            if (empty($this->$field)) {
                $errors[] = "Field '{$label}' is required";
            }
        }

        // Step 3: Approval - Prepared by (Supplier) is mandatory
        if (empty($this->prepared_by_name)) {
            $errors[] = "Field 'Prepared by - Name' is required";
        }
        if (empty($this->prepared_by_date)) {
            $errors[] = "Field 'Prepared by - Date' is required";
        }

        return $errors;
    }

    /**
     * Get display name for the record
     */
    public function getDisplayNameAttribute(): string
    {
        return "Packaging Standard - {$this->part_no} ({$this->part_name})";
    }

    /**
     * Get all form data as array for PDF generation
     */
    public function getFormDataArray(): array
    {
        // Ensure relations are loaded
        if (!$this->relationLoaded('supplier')) {
            $this->load('supplier');
        }

        return [
            'generalInformation' => [
                'documentNo' => $this->document_no,
                'revision' => $this->revision,
                'date' => $this->date?->format('Y-m-d'),
                'customer' => $this->customer,
                'partNo' => $this->part_no,
                'partName' => $this->part_name,
                'supplier' => $this->supplier_name,
                'qty' => $this->qty,
            ],
            'specifications' => [
                'packagingType' => $this->packaging_type,
                'packagingSizeMm' => $this->packaging_size_mm,
                'packagingLotPcsBox' => $this->packaging_lot_pcs_box,
                'netWeightGPc' => $this->net_weight_g_pc,
                'totalWeightKgBox' => $this->total_weight_kg_box,
            ],
            'visualizationApproval' => [
                'imagePhotographic' => $this->image_photographic,
                'imagePartIdentification' => $this->image_part_identification,
                'imageVisualisasiPacking' => $this->image_visualisasi_packing,
                'imageLabelPacking' => $this->image_label_packing,
                'revisionHistory' => $this->revision_history,
                'preparedByName' => $this->prepared_by_name,
                'preparedByDate' => $this->prepared_by_date?->format('Y-m-d'),
                'checkedByName' => $this->checked_by_name,
                'checkedByDate' => $this->checked_by_date?->format('Y-m-d'),
                'approvedByName' => $this->approved_by_name,
                'approvedByDate' => $this->approved_by_date?->format('Y-m-d'),
            ],
            'supplierInfo' => [
                'supplier' => $this->supplier?->name,
                'customer' => $this->customer ?? 'AAIJ (Purchasing & Quality Division)',
            ],
        ];
    }
}
