<?php

namespace App\Mail;

use App\Models\DocumentRequest;
use App\Models\DocumentRequestReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BundleSubmissionReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public DocumentRequest $documentRequest;
    public DocumentRequestReminder $reminder;
    public int $level;
    public ?string $recipientName;
    public array $overallProgress;
    public array $partsProgress;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        DocumentRequest $documentRequest, 
        DocumentRequestReminder $reminder,
        int $level,
        ?string $recipientName = null
    ) {
        $this->documentRequest = $documentRequest;
        $this->reminder = $reminder;
        $this->level = $level;
        $this->recipientName = $recipientName;
        $this->overallProgress = $reminder->getOverallProgressInfo();
        $this->partsProgress = $reminder->getPartsProgressInfo();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $model = $this->documentRequest->model;
        
        // Generate subject berdasarkan level dan jumlah parts
        $subject = $this->generateSubject($model);

        // Get login URL untuk supplier
        $supplierUser = $this->documentRequest->supplier->users()->first();
        $loginUrl = null;
        if ($supplierUser) {
            $loginUrl = $supplierUser->generateSignedLoginUrl($this->documentRequest->id);
        }

        // Get escalation history
        $escalationHistory = $this->getEscalationHistory();

        return $this->markdown('emails.bundle-submission-reminder')
            ->subject($subject)
            ->with([
                'documentRequest' => $this->documentRequest->load('supplier', 'requestParts'),
                'reminder' => $this->reminder,
                'level' => $this->level,
                'recipientName' => $this->recipientName,
                'overallProgress' => $this->overallProgress,
                'partsProgress' => $this->partsProgress,
                'loginUrl' => $loginUrl,
                'escalationHistory' => $escalationHistory,
            ]);
    }

    /**
     * Generate subject berdasarkan level escalation
     */
    private function generateSubject(string $model): string
    {
        $partsCount = $this->overallProgress['total_parts'];
        $partsSuffix = $partsCount > 1 ? " ({$partsCount} Parts)" : "";
        
        return match ($this->level) {
            1 => "[REMINDER] Pending Document Submission - {$model}{$partsSuffix}",
            2 => "[ESCALATION LEVEL 2] Pending Document Submission - {$model}{$partsSuffix}",
            3 => "[ESCALATION LEVEL 3] Pending Document Submission - {$model}{$partsSuffix}",
            4 => "[ESCALATION LEVEL 4] URGENT - Pending Document Submission - {$model}{$partsSuffix}",
            5 => "[ESCALATION LEVEL 5] CRITICAL - Document Submission Required - {$model}{$partsSuffix}",
            default => "Document Submission Reminder - {$model}{$partsSuffix}",
        };
    }

    /**
     * Get escalation history untuk ditampilkan di email level tinggi
     */
    private function getEscalationHistory(): array
    {
        $history = [];

        if ($this->reminder->pic_reminder_sent_at) {
            $history[] = [
                'level' => 'PIC',
                'status' => 'Reminder Sent',
                'sent_at' => $this->reminder->pic_reminder_sent_at,
            ];
        }

        if ($this->reminder->supervisor_escalation_sent_at) {
            $history[] = [
                'level' => 'Supervisor',
                'status' => 'Escalated',
                'sent_at' => $this->reminder->supervisor_escalation_sent_at,
            ];
        }

        if ($this->reminder->head_dept_escalation_sent_at) {
            $history[] = [
                'level' => 'Head Department',
                'status' => 'Escalated',
                'sent_at' => $this->reminder->head_dept_escalation_sent_at,
            ];
        }

        if ($this->reminder->gm_escalation_sent_at) {
            $history[] = [
                'level' => 'General Manager',
                'status' => 'Escalated',
                'sent_at' => $this->reminder->gm_escalation_sent_at,
            ];
        }

        return $history;
    }
}
