<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ApprovalToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_document_id',
        'user_id',
        'token',
        'division',
        'expires_at',
        'used_at',
        'invalidated_at',
        'ip_address',
        'user_agent',
        'accessed_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'invalidated_at' => 'datetime',
        'accessed_at' => 'datetime',
    ];

    /**
     * Generate new approval token
     * 
     * @param DocumentApproval $approval
     * @param string $recipientEmail
     * @param string $recipientName
     * @param array $options
     * @return static
     */
    public static function generate(
        DocumentApproval $approval,
        string $recipientEmail,
        string $recipientName = null,
        array $options = []
    ): self {
        // Generate secure random token
        $plainToken = Str::random(64);
        $hashedToken = hash('sha256', $plainToken);

        // Load request document to get document info
        $requestDocument = $approval->requestDocument;
        $documentType = $requestDocument->documentType->code ?? 'UNKNOWN';

        // Default expiry: 30 days from now
        $expiresAt = Carbon::now()->addDays($options['expires_days'] ?? 30);

        $token = self::create([
            'token' => $hashedToken,
            'plain_token' => null, // Don't store plain token
            'document_approval_id' => $approval->id,
            'request_document_id' => $requestDocument->id,
            'recipient_email' => $recipientEmail,
            'recipient_name' => $recipientName,
            'division' => $approval->division ?? $approval->scope,
            'scope' => $approval->scope,
            'document_type' => $documentType,
            'document_number' => $options['document_number'] ?? null,
            'document_version' => $options['document_version'] ?? 1,
            'expires_at' => $expiresAt,
            'notes' => $options['notes'] ?? null,
        ]);

        // Store plain token temporarily for returning (will not be saved to DB)
        $token->setAttribute('_plain_token', $plainToken);

        return $token;
    }

    /**
     * Find token by plain token string
     * 
     * @param string $plainToken
     * @return static|null
     */
    public static function findByPlainToken(string $plainToken): ?self
    {
        $hashedToken = hash('sha256', $plainToken);
        return self::where('token', $hashedToken)->first();
    }

    /**
     * Validate token and return validation result
     * 
     * @return array ['valid' => bool, 'reason' => string|null]
     */
    public function validate(): array
    {
        // Check if revoked
        if ($this->is_revoked) {
            return [
                'valid' => false,
                'reason' => 'Token has been revoked',
            ];
        }

        // Check if already used
        if ($this->used_at) {
            return [
                'valid' => false,
                'reason' => 'Token has already been used',
            ];
        }

        // Check if expired
        if ($this->isExpired()) {
            return [
                'valid' => false,
                'reason' => 'Token has expired',
            ];
        }

        // Check if document approval still pending
        if ($this->documentApproval->status !== 'PENDING') {
            return [
                'valid' => false,
                'reason' => 'Approval task is no longer pending',
            ];
        }

        return [
            'valid' => true,
            'reason' => null,
        ];
    }

    /**
     * Check if token is expired
     * 
     * @return bool
     */
    public function isExpired(): bool
    {
        return Carbon::now()->isAfter($this->expires_at);
    }

    /**
     * Check if token is valid (not expired, not used, not revoked)
     * 
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->validate()['valid'];
    }

    /**
     * Mark token as used
     * 
     * @return bool
     */
    public function markAsUsed(): bool
    {
        return $this->update([
            'used_at' => Carbon::now(),
        ]);
    }

    /**
     * Revoke token
     * 
     * @param string|null $reason
     * @return bool
     */
    public function revoke(string $reason = null): bool
    {
        return $this->update([
            'is_revoked' => true,
            'notes' => $reason ? ($this->notes ? $this->notes . "\n" . $reason : $reason) : $this->notes,
        ]);
    }

    /**
     * Track access to the token
     * 
     * @param string|null $ipAddress
     * @param string|null $userAgent
     * @return bool
     */
    public function trackAccess(string $ipAddress = null, string $userAgent = null): bool
    {
        return $this->update([
            'access_count' => $this->access_count + 1,
            'last_accessed_at' => Carbon::now(),
            'ip_address' => $ipAddress ?? $this->ip_address,
            'user_agent' => $userAgent ?? $this->user_agent,
        ]);
    }

    /**
     * Get the plain token (only available immediately after generation)
     * 
     * @return string|null
     */
    public function getPlainToken(): ?string
    {
        return $this->getAttribute('_plain_token');
    }

    /**
     * Generate approval URL
     * 
     * @return string
     */
    public function getApprovalUrl(): string
    {
        $plainToken = $this->getPlainToken() ?? 'TOKEN_NOT_AVAILABLE';
        return route('external-approval.show', ['token' => $plainToken]);
    }

    /**
     * Revoke all tokens for a document approval
     * 
     * @param int $documentApprovalId
     * @param string|null $reason
     * @return int Number of tokens revoked
     */
    public static function revokeByApproval(int $documentApprovalId, string $reason = null): int
    {
        return self::where('document_approval_id', $documentApprovalId)
            ->where('is_revoked', false)
            ->whereNull('used_at')
            ->update([
                'is_revoked' => true,
                'notes' => $reason,
            ]);
    }

    /**
     * Revoke all tokens for a request document
     * 
     * @param int $requestDocumentId
     * @param string|null $reason
     * @return int Number of tokens revoked
     */
    public static function revokeByDocument(int $requestDocumentId, string $reason = null): int
    {
        return self::where('request_document_id', $requestDocumentId)
            ->where('is_revoked', false)
            ->whereNull('used_at')
            ->update([
                'is_revoked' => true,
                'notes' => $reason,
            ]);
    }

    /**
     * Clean up expired tokens (soft delete or hard delete based on config)
     * 
     * @param int $daysOld Delete tokens expired more than X days ago
     * @return int Number of tokens deleted
     */
    public static function cleanupExpired(int $daysOld = 90): int
    {
        return self::where('expires_at', '<', Carbon::now()->subDays($daysOld))
            ->delete();
    }

    /**
     * Relationships
     */

    /**
     * Approval task this token is for
     */
    public function documentApproval()
    {
        return $this->belongsTo(DocumentApproval::class);
    }

    /**
     * Request document being approved
     */
    public function requestDocument()
    {
        return $this->belongsTo(RequestDocument::class);
    }

    /**
     * Scopes
     */

    /**
     * Scope to valid tokens only
     */
    public function scopeValid($query)
    {
        return $query->where('is_revoked', false)
            ->whereNull('used_at')
            ->where('expires_at', '>', Carbon::now());
    }

    /**
     * Scope to expired tokens
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', Carbon::now());
    }

    /**
     * Scope to unused tokens
     */
    public function scopeUnused($query)
    {
        return $query->whereNull('used_at');
    }

    /**
     * Scope to tokens for a specific division
     */
    public function scopeForDivision($query, string $division)
    {
        return $query->where('division', strtoupper($division));
    }

    /**
     * Scope to tokens for a specific document type
     */
    public function scopeForDocumentType($query, string $documentType)
    {
        return $query->where('document_type', strtoupper($documentType));
    }
}
