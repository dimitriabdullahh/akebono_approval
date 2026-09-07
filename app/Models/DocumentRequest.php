<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'model',
        'due_date',
        'access_token',
        'created_by',
        'alert_dismissed_at',
        'documents_submitted_notified_at',
    ];

    protected $dates = [
        'due_date',
        'alert_dismissed_at',
        'documents_submitted_notified_at',
        'created_at',
        'updated_at',
    ];

    /**
     * Relasi: Banyak document requests milik satu supplier
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Relasi: Satu document request memiliki banyak request parts
     */
    public function requestParts()
    {
        return $this->hasMany(RequestPart::class);
    }

    /**
     * Relasi: Satu document request memiliki banyak request documents
     */
    public function requestDocuments()
    {
        return $this->hasMany(RequestDocument::class);
    }

    /**
     * Relasi: Satu document request memiliki banyak notification logs
     */
    public function notificationLogs()
    {
        return $this->hasMany(NotificationLog::class);
    }

    /**
     * Relasi: Reminder untuk document request
     */
    public function reminder()
    {
        return $this->hasOne(DocumentRequestReminder::class);
    }

    /**
     * Relasi: User yang membuat request
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Accessor: Get first part name (backward compatibility)
     */
    public function getPartNameAttribute()
    {
        return $this->requestParts()->first()->part_name ?? '-';
    }

    /**
     * Accessor: Get first part number (backward compatibility)
     */
    public function getPartNumberAttribute()
    {
        return $this->requestParts()->first()->part_number ?? '-';
    }

    /**
     * Get formatted parts list for display
     */
    public function getPartsListAttribute()
    {
        return $this->requestParts()->get();
    }

    /**
     * Check if request has multiple parts
     */
    public function hasMultipleParts(): bool
    {
        // Use loaded relationship if available, otherwise query
        if ($this->relationLoaded('requestParts')) {
            return $this->requestParts->count() > 1;
        }
        return $this->requestParts()->count() > 1;
    }

    /**
     * Hitung progress: berapa % dokumen mandatory yang sudah APPROVED
     */
    public function getApprovalProgressAttribute()
    {
        $mandatory = $this->requestDocuments()->whereHas('documentType', function ($q) {
            $q->where('is_mandatory', true);
        })->count();

        if ($mandatory === 0) {
            return 0;
        }

        $approved = $this->requestDocuments()
            ->whereHas('documentType', function ($q) {
                $q->where('is_mandatory', true);
            })
            ->where('status', 'APPROVED')
            ->count();

        return (int) (($approved / $mandatory) * 100);
    }

    /**
     * Klasifikasi status project (3 kategori)
     */
    public function getProjectCategoryAttribute()
    {
        $mandatory = $this->requestDocuments()->whereHas('documentType', function ($q) {
            $q->where('is_mandatory', true);
        })->count();

        $submitted = $this->requestDocuments()
            ->whereHas('documentType', function ($q) {
                $q->where('is_mandatory', true);
            })
            ->whereIn('status', ['SUBMITTED', 'APPROVED', 'REVISE'])
            ->count();

        $approved = $this->requestDocuments()
            ->whereHas('documentType', function ($q) {
                $q->where('is_mandatory', true);
            })
            ->where('status', 'APPROVED')
            ->count();

        // 🔴 Belum Kirim Dokumen
        if ($submitted === 0) {
            return 'not_submitted';
        }

        // 🟢 Lengkap / Seluruhnya Approved
        if ($approved === $mandatory) {
            return 'completed';
        }

        // 🟡 Sedang Berjalan / Belum Lengkap
        return 'in_progress';
    }

    /**
     * Get outstanding status for alert box
     * Returns: 'open', 'reject', atau null (completed)
     */
    public function getOutstandingStatusAttribute()
    {
        // Load relasi jika belum di-load
        if (!$this->relationLoaded('requestDocuments')) {
            $this->load('requestDocuments.documentType');
        }

        $hasRevision = $this->requestDocuments
            ->where('status', 'REVISE')
            ->isNotEmpty();
        
        if ($hasRevision) {
            return 'reject'; // Ada dokumen yang perlu revisi
        }

        $category = $this->project_category;
        
        if ($category === 'completed') {
            return null; // Sudah complete, tidak masuk outstanding
        }

        return 'open'; // Belum submit atau submit sebagian
    }

    /**
     * Check if this request should appear in outstanding alert
     */
    public function isOutstanding(): bool
    {
        // Sudah dismissed dan belum lewat 7 hari
        if ($this->alert_dismissed_at && $this->alert_dismissed_at->gt(now()->subDays(7))) {
            return false;
        }

        $status = $this->outstanding_status;
        return $status !== null;
    }

    /**
     * Dismiss alert untuk request ini
     */
    public function dismissAlert()
    {
        $this->update(['alert_dismissed_at' => now()]);
    }

    /**
     * Get portal URL untuk langsung ke upload
     */
    public function getPortalUploadUrlAttribute()
    {
        return route('supplier.upload', ['token' => $this->access_token]);
    }
}
