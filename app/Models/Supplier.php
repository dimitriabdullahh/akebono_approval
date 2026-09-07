<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'vendor_code',
        'pic_name',
        'pic_email',
        'pic_phone',
        'supervisor_name',
        'supervisor_email',
        'head_dept_name',
        'head_dept_email',
        'gm_name',
        'gm_email',
        'director_name',
        'director_email',
        'address',
    ];

    /**
     * Relasi: Satu supplier memiliki banyak document requests
     */
    public function documentRequests()
    {
        return $this->hasMany(DocumentRequest::class);
    }

    /**
     * Relasi: Satu supplier bisa memiliki banyak users (supplier users)
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get outstanding document requests (belum complete approval)
     * Status: Open (belum submit/sebagian) atau Reject (ada revisi)
     */
    public function outstandingDocumentRequests()
    {
        return $this->hasMany(DocumentRequest::class)
            ->with(['requestDocuments.documentType', 'requestParts', 'supplier'])
            ->where(function ($query) {
                // Exclude yang sudah dismissed alert (dismissed kurang dari 7 hari yang lalu)
                $query->whereNull('alert_dismissed_at')
                    ->orWhere('alert_dismissed_at', '<=', now()->subDays(7));
            })
            ->orderBy('due_date', 'asc');
    }

    /**
     * Get count of outstanding requests
     */
    public function getOutstandingCountAttribute()
    {
        return $this->outstandingDocumentRequests()->count();
    }
}
