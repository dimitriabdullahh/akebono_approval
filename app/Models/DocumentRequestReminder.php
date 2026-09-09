<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentRequestReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_request_id',
        'reminder_level',
        'escalation_status',
        'initial_reminder_sent_at',
        'pic_reminder_sent_at',
        'supervisor_escalation_sent_at',
        'head_dept_escalation_sent_at',
        'gm_escalation_sent_at',
        'director_escalation_sent_at',
        'last_reminder_at',
        'next_reminder_at',
        'stopped_at',
    ];

    protected $dates = [
        'initial_reminder_sent_at',
        'pic_reminder_sent_at',
        'supervisor_escalation_sent_at',
        'head_dept_escalation_sent_at',
        'gm_escalation_sent_at',
        'director_escalation_sent_at',
        'last_reminder_at',
        'next_reminder_at',
        'stopped_at',
        'created_at',
        'updated_at',
    ];

    /**
     * Relasi: Reminder belongs to DocumentRequest
     */
    public function documentRequest()
    {
        return $this->belongsTo(DocumentRequest::class);
    }

    /**
     * Check if reminder should stop (semua parts sudah submitted)
     */
    public function shouldStop(): bool
    {
        $documentRequest = $this->documentRequest;
        
        // Check apakah semua parts sudah SUBMITTED
        $totalParts = $documentRequest->requestParts()->count();
        $submittedParts = $documentRequest->requestParts()
            ->where('submission_status', 'SUBMITTED')
            ->count();
        
        return $totalParts > 0 && $totalParts === $submittedParts;
    }

    /**
     * Check if reminder is already stopped
     */
    public function isStopped(): bool
    {
        return $this->escalation_status === 'STOPPED';
    }

    /**
     * Check if escalation sudah sampai level maksimal (Director)
     */
    public function isMaxEscalation(): bool
    {
        return $this->reminder_level >= 5;
    }

    /**
     * Check if ready untuk mengirim reminder berikutnya
     */
    public function isReadyForNextReminder(): bool
    {
        // Jika sudah dihentikan, tidak ready
        if ($this->isStopped()) {
            return false;
        }

        // Jika sudah escalated to director, tidak ada reminder lagi
        if ($this->escalation_status === 'ESCALATED_TO_DIRECTOR') {
            return false;
        }

        // Jika next_reminder_at belum diset, tidak ready
        if ($this->next_reminder_at === null) {
            return false;
        }

        // Siap jika sekarang sudah melewati next_reminder_at
        return now()->gte($this->next_reminder_at);
    }

    /**
     * Stop reminder
     */
    public function stop()
    {
        $this->update([
            'escalation_status' => 'STOPPED',
            'stopped_at' => now(),
            'next_reminder_at' => null,
        ]);
    }

    /**
     * Get nama level escalation
     */
    public function getLevelName(): string
    {
        $levels = config('reminder.escalation_levels');
        return $levels[$this->reminder_level] ?? 'Unknown';
    }

    /**
     * Get email recipient untuk level saat ini
     */
    public function getCurrentRecipientEmail(): ?string
    {
        $supplier = $this->documentRequest->supplier;
        
        return match ($this->reminder_level) {
            0 => $supplier->pic_email,
            1 => $supplier->pic_email,
            2 => $supplier->supervisor_email,
            3 => $supplier->head_dept_email,
            4 => $supplier->gm_email,
            5 => $supplier->director_email,
            default => null,
        };
    }

    /**
     * Get nama recipient untuk level saat ini
     */
    public function getCurrentRecipientName(): ?string
    {
        $supplier = $this->documentRequest->supplier;
        
        return match ($this->reminder_level) {
            0 => $supplier->pic_name,
            1 => $supplier->pic_name,
            2 => $supplier->supervisor_name,
            3 => $supplier->head_dept_name,
            4 => $supplier->gm_name,
            5 => $supplier->director_name,
            default => null,
        };
    }

    /**
     * Get overall progress info untuk semua parts
     */
    public function getOverallProgressInfo(): array
    {
        $documentRequest = $this->documentRequest;
        $parts = $documentRequest->requestParts;
        
        $totalParts = $parts->count();
        $submittedParts = $parts->where('submission_status', 'SUBMITTED')->count();
        $draftParts = $parts->where('submission_status', 'DRAFT')->count();
        
        $totalDocs = 0;
        $uploadedDocs = 0;
        $mandatoryTotal = 0;
        $mandatoryUploaded = 0;
        
        foreach ($parts as $part) {
            $totalDocs += $part->requestDocuments()->count();
            $uploadedDocs += $part->requestDocuments()
                ->whereNotNull('file_path')
                ->whereIn('status', ['SUBMITTED', 'APPROVED', 'REVISE'])
                ->count();
            
            $mandatoryTotal += $part->requestDocuments()
                ->whereHas('documentType', function($q) {
                    $q->where('is_mandatory', true);
                })
                ->count();
            
            $mandatoryUploaded += $part->requestDocuments()
                ->whereHas('documentType', function($q) {
                    $q->where('is_mandatory', true);
                })
                ->whereNotNull('file_path')
                ->whereIn('status', ['SUBMITTED', 'APPROVED', 'REVISE'])
                ->count();
        }
        
        return [
            'total_parts' => $totalParts,
            'submitted_parts' => $submittedParts,
            'draft_parts' => $draftParts,
            'total_documents' => $totalDocs,
            'uploaded_documents' => $uploadedDocs,
            'mandatory_total' => $mandatoryTotal,
            'mandatory_uploaded' => $mandatoryUploaded,
        ];
    }

    /**
     * Get progress info per part
     */
    public function getPartsProgressInfo(): array
    {
        $documentRequest = $this->documentRequest;
        $parts = $documentRequest->requestParts;
        
        $partsInfo = [];
        
        foreach ($parts as $part) {
            $totalDocs = $part->requestDocuments()->count();
            $uploadedDocs = $part->requestDocuments()
                ->whereNotNull('file_path')
                ->whereIn('status', ['SUBMITTED', 'APPROVED', 'REVISE'])
                ->count();
            
            $mandatoryTotal = $part->requestDocuments()
                ->whereHas('documentType', function($q) {
                    $q->where('is_mandatory', true);
                })
                ->count();
            
            $mandatoryUploaded = $part->requestDocuments()
                ->whereHas('documentType', function($q) {
                    $q->where('is_mandatory', true);
                })
                ->whereNotNull('file_path')
                ->whereIn('status', ['SUBMITTED', 'APPROVED', 'REVISE'])
                ->count();
            
            $partsInfo[] = [
                'part' => $part,
                'part_number' => $part->part_number,
                'part_name' => $part->part_name,
                'submission_status' => $part->submission_status,
                'total_documents' => $totalDocs,
                'uploaded_documents' => $uploadedDocs,
                'mandatory_total' => $mandatoryTotal,
                'mandatory_uploaded' => $mandatoryUploaded,
                'progress_percentage' => $totalDocs > 0 ? round(($uploadedDocs / $totalDocs) * 100) : 0,
            ];
        }
        
        return $partsInfo;
    }
}
