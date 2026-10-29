<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ApprovalBlastToken extends Model
{
    use HasFactory;

    protected $table = 'approval_tokens';

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
     * Generate new approval blast token
     */
    public static function generateForApproval(
        RequestDocument $requestDocument,
        User $user,
        string $division
    ): self {
        $plainToken = Str::random(64);
        $hashedToken = hash('sha256', $plainToken);

        $expiresAt = Carbon::now()->addDays(
            config('approval_blast.token_expires_in_days', 7)
        );

        $token = self::create([
            'request_document_id' => $requestDocument->id,
            'user_id' => $user->id,
            'token' => $hashedToken,
            'division' => $division,
            'expires_at' => $expiresAt,
        ]);

        // Return plain token for URL - not stored in DB
        $token->_plain_token = $plainToken;

        return $token;
    }

    /**
     * Find token by plain token string
     */
    public static function findByPlainToken(string $plainToken): ?self
    {
        $hashedToken = hash('sha256', $plainToken);
        return self::where('token', $hashedToken)->first();
    }

    /**
     * Get the plain token (only available immediately after generation)
     */
    public function getPlainToken(): ?string
    {
        return $this->getAttribute('_plain_token');
    }

    /**
     * Check if token is valid
     */
    public function isValid(): bool
    {
        return !$this->isExpired() 
            && !$this->isUsed() 
            && !$this->isInvalidated();
    }

    /**
     * Check if token is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && Carbon::now()->isAfter($this->expires_at);
    }

    /**
     * Check if token has been used
     */
    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    /**
     * Check if token has been invalidated
     */
    public function isInvalidated(): bool
    {
        return $this->invalidated_at !== null;
    }

    /**
     * Get validation status with detailed message
     */
    public function getValidationStatus(): array
    {
        if ($this->isInvalidated()) {
            return [
                'valid' => false,
                'reason' => 'Token telah dibatalkan.',
            ];
        }

        if ($this->isUsed()) {
            return [
                'valid' => false,
                'reason' => 'Token telah digunakan oleh ' . ($this->user?->name ?? 'user lain') . '.',
            ];
        }

        if ($this->isExpired()) {
            return [
                'valid' => false,
                'reason' => 'Link approval telah kedaluwarsa (' . $this->expires_at->format('d M Y') . ').',
            ];
        }

        return [
            'valid' => true,
            'reason' => null,
        ];
    }

    /**
     * Mark token as used/accessed
     */
    public function markAsUsed(): bool
    {
        return $this->update([
            'used_at' => Carbon::now(),
        ]);
    }

    /**
     * Mark token as invalidated
     */
    public function invalidate(): bool
    {
        return $this->update([
            'invalidated_at' => Carbon::now(),
        ]);
    }

    /**
     * Track first access to the token
     */
    public function trackAccess(?string $ipAddress = null, ?string $userAgent = null): bool
    {
        if ($this->accessed_at) {
            return true; // Already accessed
        }

        return $this->update([
            'accessed_at' => Carbon::now(),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * Invalidate all tokens for a document
     */
    public static function invalidateForDocument(int $requestDocumentId): int
    {
        return self::where('request_document_id', $requestDocumentId)
            ->whereNull('used_at')
            ->whereNull('invalidated_at')
            ->update(['invalidated_at' => Carbon::now()]);
    }

    /**
     * Clean up old tokens
     */
    public static function cleanup(int $daysOld = 90): int
    {
        return self::where('created_at', '<', Carbon::now()->subDays($daysOld))
            ->delete();
    }

    /**
     * Relationships
     */

    public function requestDocument()
    {
        return $this->belongsTo(RequestDocument::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */

    public function scopeValid($query)
    {
        return $query->whereNull('used_at')
            ->whereNull('invalidated_at')
            ->where('expires_at', '>', Carbon::now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', Carbon::now());
    }

    public function scopeUsed($query)
    {
        return $query->whereNotNull('used_at');
    }

    public function scopeForDivision($query, string $division)
    {
        return $query->where('division', $division);
    }

    public function scopeForDocument($query, int $requestDocumentId)
    {
        return $query->where('request_document_id', $requestDocumentId);
    }
}
