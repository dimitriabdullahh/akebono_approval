<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestPartReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_part_id',
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
        'total_documents',
        'uploaded_documents_at_last_check',
        'submission_status_at_last_check',
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
     * Relasi: Reminder belongs to RequestPart
     */
    public function requestPart()
    {
        return $this->belongsTo(RequestPart::class);
    }

    /**
     * Relasi: Reminder belongs to DocumentRequest
     */
    public function documentRequest()
    {
        return $this->belongsTo(DocumentRequest::class);
    }

    /**
     * Check if reminder should stop (bundle sudah submitted)
     */
    public function shouldStop(): bool
    {
        $part = $this->requestPart;
        
        // Bundle dianggap selesai jika status SUBMITTED
        return $part->submission_status === 'SUBMITTED';
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
        if ($this->isStopped()) {
            return false;
        }

        if ($this->isMaxEscalation() && $this->escalation_status === 'ESCALATED_TO_DIRECTOR') {
            return false;
        }

        if ($this->next_reminder_at === null) {
            return false;
        }

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
     * Get upload progress info
     */
    public function getProgressInfo(): array
    {
        $part = $this->requestPart;
        
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
        
        return [
            'total_documents' => $totalDocs,
            'uploaded_documents' => $uploadedDocs,
            'mandatory_total' => $mandatoryTotal,
            'mandatory_uploaded' => $mandatoryUploaded,
            'submission_status' => $part->submission_status,
        ];
    }
}
