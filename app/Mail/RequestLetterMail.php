<?php

namespace App\Mail;

use App\Models\DocumentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RequestLetterMail extends Mailable
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
        return $this->view('emails.request-letter')
            ->subject('Surat Request Dokumen Supplier - PT Akebono Astra Indonesia')
            ->with([
                'supplier' => $this->documentRequest->supplier,
                'request' => $this->documentRequest->load('requestParts'),
            ]);
    }
}
