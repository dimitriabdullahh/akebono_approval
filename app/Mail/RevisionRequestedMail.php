<?php

namespace App\Mail;

use App\Models\RequestDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RevisionRequestedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public RequestDocument $requestDocument;
    public $supplierUser;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(RequestDocument $requestDocument, $supplierUser = null)
    {
        $this->requestDocument = $requestDocument;
        $this->supplierUser = $supplierUser;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->requestDocument->load([
            'documentRequest.supplier',
            'documentRequest.requestParts',
            'documentType',
        ]);

        $supplier = $this->requestDocument->documentRequest->supplier;
        $documentType = $this->requestDocument->documentType->name;
        
        // Get first part number for subject
        $firstPartNumber = $this->requestDocument->documentRequest->requestParts()->first()->part_number 
            ?? $this->requestDocument->documentRequest->model;

        // Generate signed login URL if supplier user is provided
        $loginUrl = null;
        if ($this->supplierUser) {
            $loginUrl = $this->supplierUser->generateSignedLoginUrl(
                $this->requestDocument->document_request_id
            );
        }

        return $this->markdown('emails.revision-requested')
            ->subject("📝 Revisi Diperlukan: {$documentType} - {$firstPartNumber}")
            ->with([
                'requestDocument' => $this->requestDocument,
                'supplier' => $supplier,
                'request' => $this->requestDocument->documentRequest,
                'loginUrl' => $loginUrl,
            ]);
    }
}
