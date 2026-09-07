<?php

namespace App\Mail;

use App\Models\DocumentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RevisionReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public DocumentRequest $documentRequest;
    public $revisionDocs;
    public $supplierUser;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(DocumentRequest $documentRequest, $revisionDocs, $supplierUser = null)
    {
        $this->documentRequest = $documentRequest;
        $this->revisionDocs = $revisionDocs;
        $this->supplierUser = $supplierUser;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Get first part number for subject (backward compatibility)
        $firstPartNumber = $this->documentRequest->requestParts()->first()->part_number ?? $this->documentRequest->model;

        // Generate signed login URL if supplier user is provided
        $loginUrl = null;
        if ($this->supplierUser) {
            $loginUrl = $this->supplierUser->generateSignedLoginUrl($this->documentRequest->id);
        }

        return $this->markdown('emails.revision-reminder')
            ->subject('⏰ Pengingat Revisi Dokumen Supplier - ' . $firstPartNumber)
            ->with([
                'supplier' => $this->documentRequest->supplier,
                'request' => $this->documentRequest->load('requestParts'),
                'revisionDocs' => $this->revisionDocs,
                'loginUrl' => $loginUrl,
            ]);
    }
}
