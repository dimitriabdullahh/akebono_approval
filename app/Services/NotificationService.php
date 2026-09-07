<?php

namespace App\Services;

use App\Jobs\ApprovalCompletedNotificationJob;
use App\Jobs\DocumentsSubmittedNotificationJob;
use App\Jobs\RevisionRequestedNotificationJob;
use App\Jobs\RevisionReminderJob;
use App\Jobs\SendRequestLetterJob;
use App\Models\DocumentRequest;
use App\Models\NotificationLog;
use App\Models\RequestDocument;

class NotificationService
{
    /**
     * Send request letter to supplier
     */
    public function sendRequestLetter(DocumentRequest $request): void
    {
        SendRequestLetterJob::dispatch($request);
    }

    /**
     * Notify reviewers when all mandatory documents submitted
     */
    public function notifyDocumentsSubmitted(DocumentRequest $request): void
    {
        DocumentsSubmittedNotificationJob::dispatch($request);
    }

    /**
     * Notify supplier when document revision is requested
     */
    public function notifyRevisionRequested(RequestDocument $requestDocument): void
    {
        RevisionRequestedNotificationJob::dispatch($requestDocument);
    }

    /**
     * Send revision reminder
     */
    public function sendRevisionReminder(DocumentRequest $request): void
    {
        RevisionReminderJob::dispatch($request);
    }

    /**
     * Notify when all documents approved
     */
    public function notifyApprovalCompleted(DocumentRequest $request): void
    {
        ApprovalCompletedNotificationJob::dispatch($request);
    }

    /**
     * Log notification
     */
    public function logNotification(
        int $documentRequestId,
        string $type,
        string $recipient,
        string $subject,
        string $status = NotificationLog::STATUS_SENT,
        ?string $errorMessage = null
    ): NotificationLog {
        return NotificationLog::create([
            'document_request_id' => $documentRequestId,
            'type' => $type,
            'recipient' => $recipient,
            'subject' => $subject,
            'sent_at' => now(),
            'status' => $status,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Get notification history untuk document request
     */
    public function getNotificationHistory(int $documentRequestId)
    {
        return NotificationLog::where('document_request_id', $documentRequestId)
            ->orderByDesc('sent_at')
            ->get();
    }
}
