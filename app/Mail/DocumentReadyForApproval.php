<?php

namespace App\Mail;

use App\Models\Employee;
use App\Models\RequestDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentReadyForApproval extends Mailable
{
    use Queueable, SerializesModels;

    public RequestDocument $requestDocument;
    public Employee $approver;

    /**
     * Create a new message instance.
     *
     * @param RequestDocument $requestDocument
     * @param Employee $approver
     */
    public function __construct(RequestDocument $requestDocument, Employee $approver)
    {
        $this->requestDocument = $requestDocument;
        $this->approver = $approver;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $documentType = $this->requestDocument->documentType->name;
        $supplier = $this->requestDocument->documentRequest->supplier->name;
        $model = $this->requestDocument->documentRequest->model;

        return $this->subject("Document Ready for Approval: {$documentType} - {$supplier}")
            ->markdown('emails.document-ready-for-approval', [
                'requestDocument' => $this->requestDocument,
                'approver' => $this->approver,
                'documentType' => $documentType,
                'supplier' => $supplier,
                'model' => $model,
                'reviewUrl' => route('review.show', $this->requestDocument),
            ]);
    }
}
