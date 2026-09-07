<?php

namespace App\Mail;

use App\Models\DocumentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApprovalCompletedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public DocumentRequest $documentRequest;
    public string $portalUrl;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(DocumentRequest $documentRequest, string $portalUrl)
    {
        $this->documentRequest = $documentRequest;
        $this->portalUrl = $portalUrl;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $approvedDocs = $this->documentRequest->requestDocuments()
            ->where('status', 'APPROVED')
            ->with('documentType')
            ->get();

        // Get first part number for subject (backward compatibility)
        $firstPartNumber = $this->documentRequest->requestParts()->first()->part_number ?? $this->documentRequest->model;

        return $this->markdown('emails.approval-completed')
            ->subject('✅ Approval Selesai - Semua Dokumen Telah Disetujui - ' . $firstPartNumber)
            ->with([
                'supplier' => $this->documentRequest->supplier,
                'request' => $this->documentRequest->load('requestParts'),
                'approvedDocs' => $approvedDocs,
                'portalUrl' => $this->portalUrl,
            ]);
    }
}
