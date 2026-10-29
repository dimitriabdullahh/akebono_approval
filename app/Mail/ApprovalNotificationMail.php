<?php

namespace App\Mail;

use App\Models\RequestDocument;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Markdown;
use Illuminate\Queue\SerializesModels;

class ApprovalNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public RequestDocument $requestDocument;
    public User $recipient;
    public string $division;
    public string $token;

    /**
     * Create a new message instance.
     */
    public function __construct(
        RequestDocument $requestDocument,
        User $recipient,
        string $division,
        string $token
    ) {
        $this->requestDocument = $requestDocument;
        $this->recipient = $recipient;
        $this->division = $division;
        $this->token = $token;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $documentType = $this->requestDocument->documentType;
        $supplier = $this->requestDocument->documentRequest->supplier;

        $subject = "[Approval Diperlukan] {$documentType->name} - {$supplier->name}";

        return $this
            ->subject($subject)
            ->markdown('emails.approval-notification', [
                'requestDocument' => $this->requestDocument,
                'recipient' => $this->recipient,
                'division' => $this->division,
                'token' => $this->token,
                'approvalUrl' => route('approval.show', ['token' => $this->token]),
                'expiresAt' => config('approval_blast.token_expires_in_days', 7),
            ]);
    }
}
