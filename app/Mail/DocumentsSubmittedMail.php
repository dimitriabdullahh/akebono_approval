<?php

namespace App\Mail;

use App\Models\DocumentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentsSubmittedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public DocumentRequest $documentRequest;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(DocumentRequest $documentRequest)
    {
        $this->documentRequest = $documentRequest;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $submittedDocs = $this->documentRequest->requestDocuments()
            ->whereIn('status', ['SUBMITTED', 'REVISE', 'APPROVED'])
            ->with('documentType')
            ->get();

        // Get first part number for subject (backward compatibility)
        $firstPartNumber = $this->documentRequest->requestParts()->first()->part_number ?? $this->documentRequest->model;

        return $this->markdown('emails.documents-submitted')
            ->subject('Notifikasi: Dokumen Supplier Telah Lengkap - ' . $firstPartNumber)
            ->with([
                'supplier' => $this->documentRequest->supplier,
                'request' => $this->documentRequest->load('requestParts'),
                'submittedDocs' => $submittedDocs,
            ]);
    }
}
