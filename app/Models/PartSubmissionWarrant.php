<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartSubmissionWarrant extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_document_id',
        'supplier_id',
        // Step 1: Informasi Umum
        'part_name',
        'part_number',
        'safety_government_regulation',
        'engineering_drawing_change_level',
        'engineering_drawing_change_date',
        'additional_engineering_changes',
        'additional_engineering_changes_date',
        'shown_on_drawing_no',
        'purchase_order_no',
        'weight_kg',
        'checking_aid_no',
        'process_engineering_change_level',
        'process_engineering_change_date',
        // Step 2: Supplier & Submission Information
        'supplier_name',
        'supplier_code',
        'street_address',
        'city',
        'state',
        'zip',
        'customer_name_division',
        'buyer_buyer_code',
        'application',
        'submission_information',
        'contains_prohibited_substances',
        'plastic_has_iso_marking',
        // Step 3: Reason & Results
        'reason_for_submission',
        'submission_results',
        'meets_all_requirements',
        'explanation_if_not_meet',
        // Step 4: Deklarasi & Kirim
        'explanation_comments',
        'print_name',
        'title',
        'phone_no',
        'fax_no',
        'signature_qr_data',
        'signature_date',
        // Internal Akebono (readonly for supplier)
        'internal_decision',
        'internal_approved_by',
        'internal_approved_date',
        'internal_notes',
        // Status & metadata
        'status',
        'pdf_file_path',
        'form_data_json',
        'submitted_at',
        'approved_at',
        'rejected_at',
    ];

    protected $dates = [
        'engineering_drawing_change_date',
        'additional_engineering_changes_date',
        'process_engineering_change_date',
        'signature_date',
        'internal_approved_date',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'engineering_drawing_change_date' => 'date',
        'additional_engineering_changes_date' => 'date',
        'process_engineering_change_date' => 'date',
        'signature_date' => 'date',
        'internal_approved_date' => 'date',
        'weight_kg' => 'decimal:3',
        'safety_government_regulation' => 'boolean',
        'contains_prohibited_substances' => 'boolean',
        'plastic_has_iso_marking' => 'boolean',
        'meets_all_requirements' => 'boolean',
        'submission_information' => 'json',
        'reason_for_submission' => 'json',
        'submission_results' => 'json',
        'form_data_json' => 'json',
    ];

    /**
     * Relasi: Belongs to RequestDocument
     * PSW attached to PSW document type
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
     * Check if PSW is in draft status
     */
    public function isDraft(): bool
    {
        return $this->status === 'DRAFT';
    }

    /**
     * Check if PSW is submitted
     */
    public function isSubmitted(): bool
    {
        return in_array($this->status, ['SUBMITTED', 'PENDING_REVIEW']);
    }

    /**
     * Check if PSW is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'APPROVED';
    }

    /**
     * Check if PSW is rejected
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
            'part_name' => 'Part Name',
            'part_number' => 'Part Number',
        ];

        foreach ($mandatoryFields as $field => $label) {
            if (empty($this->$field)) {
                $errors[] = "Field '{$label}' is required";
            }
        }

        // Step 2: Supplier & Submission Information
        if (empty($this->supplier_name)) {
            $errors[] = "Field 'Supplier Name' is required";
        }
        if (empty($this->supplier_code)) {
            $errors[] = "Field 'Supplier Code' is required";
        }

        // Step 4: Deklarasi - mandatory fields
        if (empty($this->print_name)) {
            $errors[] = "Field 'Print Name' is required";
        }
        if (empty($this->title)) {
            $errors[] = "Field 'Title' is required";
        }
        if (empty($this->signature_date)) {
            $errors[] = "Field 'Signature Date' is required";
        }

        return $errors;
    }

    /**
     * Get display name for the record
     */
    public function getDisplayNameAttribute(): string
    {
        return "Part Submission Warrant - {$this->part_number} ({$this->part_name})";
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
                'partName' => $this->part_name,
                'partNumber' => $this->part_number,
                'safetyGovernmentRegulation' => $this->safety_government_regulation,
                'engineeringDrawingChangeLevel' => $this->engineering_drawing_change_level,
                'engineeringDrawingChangeDate' => $this->engineering_drawing_change_date?->format('Y-m-d'),
                'additionalEngineeringChanges' => $this->additional_engineering_changes,
                'additionalEngineeringChangesDate' => $this->additional_engineering_changes_date?->format('Y-m-d'),
                'shownOnDrawingNo' => $this->shown_on_drawing_no,
                'purchaseOrderNo' => $this->purchase_order_no,
                'weightKg' => $this->weight_kg,
                'checkingAidNo' => $this->checking_aid_no,
                'processEngineeringChangeLevel' => $this->process_engineering_change_level,
                'processEngineeringChangeDate' => $this->process_engineering_change_date?->format('Y-m-d'),
            ],
            'supplierSubmissionInfo' => [
                'supplierName' => $this->supplier_name,
                'supplierCode' => $this->supplier_code,
                'streetAddress' => $this->street_address,
                'city' => $this->city,
                'state' => $this->state,
                'zip' => $this->zip,
                'customerNameDivision' => $this->customer_name_division,
                'buyerBuyerCode' => $this->buyer_buyer_code,
                'application' => $this->application,
                'submissionInformation' => $this->submission_information,
                'containsProhibitedSubstances' => $this->contains_prohibited_substances,
                'plasticHasIsoMarking' => $this->plastic_has_iso_marking,
            ],
            'reasonResults' => [
                'reasonForSubmission' => $this->reason_for_submission,
                'submissionResults' => $this->submission_results,
                'meetsAllRequirements' => $this->meets_all_requirements,
                'explanationIfNotMeet' => $this->explanation_if_not_meet,
            ],
            'declaration' => [
                'explanationComments' => $this->explanation_comments,
                'printName' => $this->print_name,
                'title' => $this->title,
                'phoneNo' => $this->phone_no,
                'faxNo' => $this->fax_no,
                'signatureQrData' => $this->signature_qr_data,
                'signatureDate' => $this->signature_date?->format('Y-m-d'),
            ],
            'internalAkebono' => [
                'decision' => $this->internal_decision,
                'approvedBy' => $this->internal_approved_by,
                'approvedDate' => $this->internal_approved_date?->format('Y-m-d'),
                'notes' => $this->internal_notes,
            ],
            'supplierInfo' => [
                'supplier' => $this->supplier?->name,
            ],
        ];
    }
}
