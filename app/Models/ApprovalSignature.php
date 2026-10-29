<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalSignature extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'approval_id',
        'token',
        'verification_code',
        'barcode_path',
        'verified_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'verified_at' => 'datetime',
    ];

    /**
     * Relation: Signature belongs to a document approval
     */
    public function approval()
    {
        return $this->belongsTo(DocumentApproval::class, 'approval_id');
    }

    /**
     * Get verification URL for this signature
     */
    public function getVerificationUrlAttribute(): string
    {
        return route('signature.verify', ['token' => $this->token]);
    }

    /**
     * Get full barcode URL for embedding in PDFs
     */
    public function getBarcodeUrlAttribute(): ?string
    {
        if (!$this->barcode_path) {
            return null;
        }

        // Return full path to barcode image
        return storage_path('app/' . $this->barcode_path);
    }

    /**
     * Mark this signature as verified
     */
    public function markAsVerified(): void
    {
        $this->update(['verified_at' => now()]);
    }

    /**
     * Check if signature has been verified
     */
    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    /**
     * Get verification data for display
     */
    public function getVerificationData(): array
    {
        $approval = $this->approval()->with([
            'requestDocument.documentType',
            'requestDocument.requestPart',
            'requestDocument.documentRequest.supplier',
            'employee'
        ])->first();

        if (!$approval) {
            return [];
        }

        $document = $approval->requestDocument;
        $employee = $approval->employee;

        return [
            'valid' => true,
            'document_type' => $approval->document_type ?? 'N/A',
            'document_reference' => $document->id ?? 'N/A',
            'part_number' => $document->requestPart->part_number ?? $document->documentRequest->part_number ?? 'N/A',
            'part_name' => $document->requestPart->part_name ?? $document->documentRequest->part_name ?? 'N/A',
            'supplier' => $document->documentRequest->supplier->name ?? 'N/A',
            'revision' => $approval->revision_number ?? 1,
            'approval_cycle' => $approval->approval_cycle ?? 1,
            'stage' => $approval->stage ?? ($approval->role ?? 'N/A'),
            'division' => $approval->division ?? 'N/A',
            'employee_name' => $employee->name ?? 'N/A',
            'npk' => $employee->npk ?? 'N/A',
            'role' => $approval->role ?? 'N/A',
            'action' => $this->getActionLabel($approval),
            'timestamp' => $approval->approved_at ?? $approval->created_at,
            'status' => $approval->status ?? 'N/A',
            'verification_code' => $this->verification_code,
        ];
    }

    /**
     * Get human-readable action label
     */
    private function getActionLabel($approval): string
    {
        if ($approval->stage === 'RECEIVED') {
            return 'Received';
        }

        if ($approval->role === 'Checker') {
            return 'Checked';
        }

        if ($approval->role === 'Approver') {
            return 'Approved';
        }

        return 'Action';
    }
}
