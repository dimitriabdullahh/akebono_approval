<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\URL;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'supplier_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * User roles constant
     */
    const ROLE_SUPPLIER = 'supplier';
    const ROLE_ADMIN_PURCHASING = 'admin_purchasing';
    const ROLE_ADMIN_QA = 'admin_qa';
    const ROLE_ADMIN_PPC = 'admin_ppc';

    /**
     * Check if user is QA admin
     */
    public function isAdminQA()
    {
        return $this->role === self::ROLE_ADMIN_QA;
    }

    /**
     * Check if user is Purchasing admin
     */
    public function isAdminPurchasing()
    {
        return $this->role === self::ROLE_ADMIN_PURCHASING;
    }

    /**
     * Check if user is PPC admin
     */
    public function isAdminPPC()
    {
        return $this->role === self::ROLE_ADMIN_PPC;
    }

    /**
     * Check if user is Supplier
     */
    public function isSupplier()
    {
        return $this->role === self::ROLE_SUPPLIER;
    }

    /**
     * Check if user is any internal admin
     */
    public function isInternalAdmin()
    {
        return in_array($this->role, [
            self::ROLE_ADMIN_QA,
            self::ROLE_ADMIN_PURCHASING,
            self::ROLE_ADMIN_PPC,
        ]);
    }

    /**
     * Relasi: User yang membuat document requests
     */
    public function createdRequests()
    {
        return $this->hasMany(DocumentRequest::class, 'created_by');
    }

    /**
     * Relasi: Supplier untuk user dengan role supplier
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Relasi: User yang me-review documents
     */
    public function reviewedDocuments()
    {
        return $this->hasMany(RequestDocument::class, 'reviewed_by');
    }

    /**
     * Relasi: User update revision configs
     */
    public function revisionConfigs()
    {
        return $this->hasMany(RevisionConfig::class, 'updated_by');
    }

    /**
     * Get user scope berdasarkan role untuk document review
     * Sesuai Implementation Document Section 5 RBAC
     */
    public function getReviewScope(): ?string
    {
        return match ($this->role) {
            self::ROLE_ADMIN_QA => 'QA',
            self::ROLE_ADMIN_PURCHASING => 'PURCHASING',
            self::ROLE_ADMIN_PPC => 'PPC',
            default => null,
        };
    }

    /**
     * Check apakah user dapat melakukan create request
     * Hanya Admin QA yang dapat membuat request
     */
    public function canCreateRequest(): bool
    {
        return $this->role === self::ROLE_ADMIN_QA;
    }

    /**
     * Check apakah user dapat mengkonfigurasi deadline revisi
     * Semua admin internal dapat mengubah konfigurasi
     */
    public function canConfigureDeadline(): bool
    {
        return $this->isInternalAdmin();
    }

    /**
     * Check apakah user dapat trigger ulang notifikasi email
     * Semua admin internal dapat trigger ulang notifikasi
     */
    public function canTriggerNotification(): bool
    {
        return $this->isInternalAdmin();
    }

    /**
     * Check apakah user memiliki full access ke dashboard
     * Semua admin internal memiliki full access ke dashboard
     */
    public function hasDashboardFullAccess(): bool
    {
        return $this->isInternalAdmin();
    }

    /**
     * Generate signed URL for auto-login to document upload page
     * Link ini akan otomatis login user dan redirect ke halaman upload dokumen
     * 
     * @param int $requestId ID dari DocumentRequest
     * @param int $expiresInHours Durasi link valid dalam jam (default 72 jam = 3 hari)
     * @return string Signed URL untuk auto-login
     */
    public function generateSignedLoginUrl($requestId, $expiresInHours = 72): string
    {
        return URL::temporarySignedRoute(
            'auto-login',
            now()->addHours($expiresInHours),
            [
                'userId' => $this->id,
                'requestId' => $requestId
            ]
        );
    }
}
