<?php

namespace App\Mail;

use App\Models\DocumentRequest;
use App\Models\DocumentRequestReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NoSubmissionReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public DocumentRequest $documentRequest;
    public DocumentRequestReminder $reminder;
    public int $level;
    public ?string $recipientName;

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
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Get first part untuk subject
        $firstPart = $this->documentRequest->requestParts()->first();
        $partNumber = $firstPart ? $firstPart->part_number : $this->documentRequest->model;

        // Hitung total dan mandatory documents
        $totalDocs = $this->documentRequest->requestDocuments()->count();
        $mandatoryDocs = $this->documentRequest->requestDocuments()
            ->whereHas('documentType', function ($q) {
                $q->where('is_mandatory', true);
            })
            ->count();

        // Generate subject berdasarkan level
        $subject = $this->generateSubject($partNumber);

        // Get login URL untuk supplier
        $supplierUser = $this->documentRequest->supplier->users()->first();
        $loginUrl = null;
        if ($supplierUser) {
            $loginUrl = $supplierUser->generateSignedLoginUrl($this->documentRequest->id);
        }

        // Get escalation history
        $escalationHistory = $this->getEscalationHistory();

        return $this->markdown('emails.no-submission-reminder')
            ->subject($subject)
            ->with([
                'request' => $this->documentRequest->load('requestParts', 'supplier'),
                'reminder' => $this->reminder,
                'level' => $this->level,
                'recipientName' => $this->recipientName,
                'totalDocs' => $totalDocs,
                'mandatoryDocs' => $mandatoryDocs,
                'loginUrl' => $loginUrl,
                'escalationHistory' => $escalationHistory,
            ]);
    }

    /**
     * Generate subject berdasarkan level escalation
     */
    private function generateSubject(string $partNumber): string
    {
        return match ($this->level) {
            1 => "[Reminder] Document Submission Required - {$partNumber}",
            2 => "[Escalation Level 2] Supplier Document Submission Pending - {$partNumber}",
            3 => "[Escalation Level 3] Supplier Document Submission Pending - {$partNumber}",
            4 => "[Escalation Level 4] Urgent - Supplier Document Submission Pending - {$partNumber}",
            5 => "[Escalation Level 5] Critical - Supplier Has Not Submitted Documents - {$partNumber}",
            default => "Document Submission Reminder - {$partNumber}",
        };
    }

    /**
     * Get escalation history untuk ditampilkan di email level 5
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
