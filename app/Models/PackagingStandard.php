<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackagingStandard extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_document_id',
        'supplier_id',
        'company_logo',
        // Step 1: Informasi Umum
        'document_no',
        'revision',
        'date',
        'customer',
        'model',
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
        'image_of_packaging',
        'part_identification_point',
        'revision_history',
        'prepared_by_name',
        'prepared_by_date',
        'checked_by_name',
        'checked_by_date',
        'approved_by_name',
        'approved_by_date',
        // QR Code Approval Fields
        'approved_by_npk',
        'approved_by_jabatan',
        'approved_qr_data',
        'checked_by_npk',
        'checked_by_jabatan',
        'checked_qr_data',
        'prepared_by_npk',
        'prepared_by_jabatan',
        'prepared_qr_data',
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
     * Relasi: Has Many PackagingStandardImages
     */
    public function images()
    {
        return $this->hasMany(PackagingStandardImage::class);
    }

    /**
     * Get images by type
     */
    public function imagesByType($type)
    {
        return $this->images()->where('image_type', $type)->orderBy('order')->get();
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
            'date' => 'Date',
            'part_no' => 'Part No.',
            'part_name' => 'Part Name',
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
        if (!$this->relationLoaded('supplier')) {
            $this->load('supplier');
        }

        $rev = $this->revision;

        return [
            'id' => $this->id,
            'document_no' => $this->document_no,
            'revision' => $rev,
            'date' => $this->date?->format('Y-m-d'),
            'customer' => $this->customer,
            'part_no' => $this->part_no,
            'part_name' => $this->part_name,
            'supplier_name' => $this->supplier_name,
            'qty' => $this->qty,
            'packaging_type' => $this->packaging_type,
            'packaging_size_mm' => $this->packaging_size_mm,
            'packaging_lot_pcs_box' => $this->packaging_lot_pcs_box,
            'net_weight_g_pc' => $this->net_weight_g_pc,
            'total_weight_kg_box' => $this->total_weight_kg_box,
            'prepared_by_name' => $this->prepared_by_name,
            'prepared_by_date' => $this->prepared_by_date?->format('Y-m-d'),
            'checked_by_name' => $this->checked_by_name,
            'checked_by_date' => $this->checked_by_date?->format('Y-m-d'),
            'approved_by_name' => $this->approved_by_name,
            'approved_by_date' => $this->approved_by_date?->format('Y-m-d'),
            // QR Approval fields
            'prepared_by_nama' => $this->prepared_by_name,
            'prepared_by_npk' => $this->prepared_by_npk,
            'prepared_by_jabatan' => $this->prepared_by_jabatan,
            'checked_by_nama' => $this->checked_by_name,
            'checked_by_npk' => $this->checked_by_npk,
            'checked_by_jabatan' => $this->checked_by_jabatan,
            'approved_by_nama' => $this->approved_by_name,
            'approved_by_npk' => $this->approved_by_npk,
            'approved_by_jabatan' => $this->approved_by_jabatan,
            'company_logo' => $this->company_logo,
            'status' => $this->status,
            'form_data_json' => $this->form_data_json,
        ];
    }

    /**
     * Get form data for PDF generation (nested structure for compatibility)
     */
    public function getFormDataForPdf(): array
    {
        if (!$this->relationLoaded('supplier')) {
            $this->load('supplier');
        }

        $materials = [];
        if ($this->form_data_json && isset($this->form_data_json['materials'])) {
            $materials = $this->form_data_json['materials'];
        }

        $rev = $this->revision;
        $revHistory = $this->revision_history;

        return [
            'generalInformation' => [
                'documentNo' => $this->document_no,
                'revision' => $rev,
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
            'materials' => $materials,
            'visualizationApproval' => [
                'imageOfPackaging' => $this->image_of_packaging,
                'partIdentificationPoint' => $this->part_identification_point,
                'revisionHistory' => $revHistory,
                'preparedByName' => $this->prepared_by_name,
                'preparedByDate' => $this->prepared_by_date?->format('Y-m-d'),
                'checkedByName' => $this->checked_by_name,
                'checkedByDate' => $this->checked_by_date?->format('Y-m-d'),
                'approvedByName' => $this->approved_by_name,
                'approvedByDate' => $this->approved_by_date?->format('Y-m-d'),
            ],
            'supplierInfo' => [
                'supplier' => $this->supplier?->name,
                'customer' => $this->customer ?? 'PT.AKEBONO BRAKE ASTRA INDONESIA',
            ],
        ];
    }
}
