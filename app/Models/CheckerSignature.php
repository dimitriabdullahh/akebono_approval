<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CheckerSignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_document_id',
        'employee_id',
        'signature_type',
        'token',
        'verification_code',
        'employee_name',
        'employee_npk',
        'division',
        'role',
        'document_type',
        'document_reference',
        'action_at',
        'verification_count',
        'last_verified_at',
    ];

    protected $casts = [
        'action_at' => 'datetime',
        'last_verified_at' => 'datetime',
        'verification_count' => 'integer',
    ];

    /**
     * Signature types
     */
    const TYPE_RECEIVER = 'RECEIVER';
    const TYPE_CHECKER = 'CHECKER';
    const TYPE_APPROVER = 'APPROVER';

    /**
     * Relation: Belongs to RequestDocument
     */
    public function requestDocument(): BelongsTo
    {
        return $this->belongsTo(RequestDocument::class);
    }

    /**
     * Relation: Belongs to Employee
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Generate a new signature with token and verification code
     *
     * @param RequestDocument $requestDocument
     * @param Employee $employee
     * @param string $signatureType
     * @return static
     */
    public static function generate(
        RequestDocument $requestDocument,
        Employee $employee,
        string $signatureType
    ): self {
        // Generate unique token (URL-safe)
        $token = Str::random(64);
        
        // Generate verification code (human-readable, alphanumeric)
        $verificationCode = strtoupper(Str::random(12));
        
        // Get document reference
        $documentReference = self::getDocumentReference($requestDocument);
        
        return self::create([
            'request_document_id' => $requestDocument->id,
            'employee_id' => $employee->id,
            'signature_type' => $signatureType,
            'token' => $token,
            'verification_code' => $verificationCode,
            'employee_name' => $employee->name,
            'employee_npk' => $employee->npk,
            'division' => $employee->division,
            'role' => $employee->role,
            'document_type' => $requestDocument->documentType->name,
            'document_reference' => $documentReference,
            'action_at' => now(),
        ]);
    }

    /**
     * Get document reference based on document type
     *
     * @param RequestDocument $requestDocument
     * @return string|null
     */
    private static function getDocumentReference(RequestDocument $requestDocument): ?string
    {
        $docType = $requestDocument->documentType->code;
        
        if ($docType === 'IS' && $requestDocument->inspectionStandard) {
            return $requestDocument->inspectionStandard->part_no . ' - ' . $requestDocument->inspectionStandard->part_name;
        }
        
        if ($docType === 'PACKAGING_STANDARD' && $requestDocument->packagingStandard) {
            return $requestDocument->packagingStandard->part_no . ' - ' . $requestDocument->packagingStandard->part_name;
        }
        
        if ($docType === 'PSW' && $requestDocument->partSubmissionWarrant) {
            return $requestDocument->partSubmissionWarrant->part_number . ' - ' . $requestDocument->partSubmissionWarrant->part_name;
        }
        
        return $requestDocument->documentRequest->model;
    }

    /**
     * Get verification URL for QR code
     *
     * @return string
     */
    public function getVerificationUrlAttribute(): string
    {
        return route('signature.verify', ['token' => $this->token]);
    }

    /**
     * Increment verification count
     *
     * @return void
     */
    public function recordVerification(): void
    {
        $this->increment('verification_count');
        $this->update(['last_verified_at' => now()]);
    }

    /**
     * Find signature by token
     *
     * @param string $token
     * @return static|null
     */
    public static function findByToken(string $token): ?self
    {
        return self::where('token', $token)->first();
    }

    /**
     * Find signature by verification code
     *
     * @param string $code
     * @return static|null
     */
    public static function findByVerificationCode(string $code): ?self
    {
        return self::where('verification_code', strtoupper($code))->first();
    }

    /**
     * Get signature type label
     *
     * @return string
     */
    public function getSignatureTypeLabelAttribute(): string
    {
        return match($this->signature_type) {
            self::TYPE_RECEIVER => 'Received',
            self::TYPE_CHECKER => 'Checked',
            self::TYPE_APPROVER => 'Approved',
            default => $this->signature_type,
        };
    }

    /**
     * Get badge color for signature type
     *
     * @return string
     */
    public function getSignatureTypeColorAttribute(): string
    {
        return match($this->signature_type) {
            self::TYPE_RECEIVER => 'info',
            self::TYPE_CHECKER => 'primary',
            self::TYPE_APPROVER => 'success',
            default => 'secondary',
        };
    }
}
