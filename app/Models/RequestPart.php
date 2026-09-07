<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestPart extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_request_id',
        'part_name',
        'part_number',
        'submission_status',
        'last_saved_at',
        'submitted_at',
        'submitted_by',
    ];

    protected $dates = [
        'last_saved_at',
        'submitted_at',
        'created_at',
        'updated_at',
    ];

    /**
     * Relasi: Banyak request parts milik satu document request
     */
    public function documentRequest()
    {
        return $this->belongsTo(DocumentRequest::class);
    }

    /**
     * Relasi: Satu part memiliki banyak request documents
     */
    public function requestDocuments()
    {
        return $this->hasMany(RequestDocument::class);
    }

    /**
     * Relasi: User yang submit dokumen
     */
    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Relasi: Reminder untuk part ini
     */
    public function reminder()
    {
        return $this->hasOne(RequestPartReminder::class);
    }

    /**
     * Get jumlah dokumen yang belum diupload untuk part ini
     */
    public function getPendingUploadCountAttribute()
    {
        return $this->requestDocuments()->where('status', 'PENDING')->count();
    }

    /**
     * Get progress upload dokumen untuk part ini (dalam persen)
     */
    public function getUploadProgressAttribute()
    {
        $total = $this->requestDocuments()->count();
        if ($total === 0) {
            return 0;
        }
        
        $uploaded = $this->requestDocuments()
            ->whereIn('status', ['SUBMITTED', 'APPROVED'])
            ->count();
        
        return round(($uploaded / $total) * 100);
    }

    /**
     * Check if all mandatory documents are uploaded
     */
    public function allMandatoryDocumentsUploaded()
    {
        $mandatoryDocs = $this->requestDocuments()
            ->whereHas('documentType', function($query) {
                $query->where('is_mandatory', true);
            })
            ->get();

        // Check if all mandatory documents have files
        foreach ($mandatoryDocs as $doc) {
            if ($doc->status === 'PENDING' || empty($doc->file_path)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get count of mandatory documents that are not uploaded
     */
    public function getMandatoryPendingCountAttribute()
    {
        return $this->requestDocuments()
            ->whereHas('documentType', function($query) {
                $query->where('is_mandatory', true);
            })
            ->where(function($query) {
                $query->where('status', 'PENDING')
                      ->orWhereNull('file_path');
            })
            ->count();
    }

    /**
     * Get count of mandatory documents that are uploaded
     */
    public function getMandatoryUploadedCountAttribute()
    {
        return $this->requestDocuments()
            ->whereHas('documentType', function($query) {
                $query->where('is_mandatory', true);
            })
            ->whereNotNull('file_path')
            ->whereIn('status', ['SUBMITTED', 'APPROVED'])
            ->count();
    }

    /**
     * Get total count of mandatory documents
     */
    public function getMandatoryTotalCountAttribute()
    {
        return $this->requestDocuments()
            ->whereHas('documentType', function($query) {
                $query->where('is_mandatory', true);
            })
            ->count();
    }

    /**
     * Check if submission is in draft status
     */
    public function isDraft()
    {
        return $this->submission_status === 'DRAFT';
    }

    /**
     * Check if submission is submitted
     */
    public function isSubmitted()
    {
        return $this->submission_status === 'SUBMITTED';
    }

    /**
     * Check if ready to submit (all mandatory docs uploaded)
     */
    public function canSubmit()
    {
        return $this->allMandatoryDocumentsUploaded() && $this->isDraft();
    }

    /**
     * Create document placeholders for this part
     * 
     * @deprecated This method is deprecated. Documents are now created during request creation 
     *             based on Akebono's selection. Use RequestService::createRequestWithParts() instead.
     * @throws \Exception Always throws exception to prevent accidental usage
     */
    public function createDocumentPlaceholders()
    {
        throw new \Exception(
            'Method deprecated: createDocumentPlaceholders() should not be called directly. ' .
            'Documents are created during request creation based on selected document types. ' .
            'Use RequestService::createRequestWithParts() instead.'
        );
    }
}
