<?php

namespace App\Mail;

use App\Models\DocumentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentsSubmittedToAkebonoMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public DocumentRequest $documentRequest;
    public int $submittedCount;
    public array $submittedDocuments;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(DocumentRequest $documentRequest, int $submittedCount, array $submittedDocuments)
    {
        $this->documentRequest = $documentRequest;
        $this->submittedCount = $submittedCount;
        $this->submittedDocuments = $submittedDocuments;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Get all documents status summary
        $allDocs = $this->documentRequest->requestDocuments()->with('documentType')->get();
        
        $summary = [
            'total' => $allDocs->count(),
            'submitted' => $allDocs->whereIn('status', ['SUBMITTED', 'REVISE', 'APPROVED'])->count(),
            'pending' => $allDocs->where('status', 'PENDING')->count(),
            'approved' => $allDocs->where('status', 'APPROVED')->count(),
        ];

        // Get first part number for subject (backward compatibility)
        $firstPartNumber = $this->documentRequest->requestParts()->first()->part_number ?? $this->documentRequest->model;

        return $this->markdown('emails.documents-submitted-to-akebono')
            ->subject('Notifikasi Submit Dokumen: ' . $this->submittedCount . ' Dokumen dari ' . $this->documentRequest->supplier->name . ' - ' . $firstPartNumber)
            ->with([
                'supplier' => $this->documentRequest->supplier,
                'request' => $this->documentRequest->load('requestParts'),
                'submittedCount' => $this->submittedCount,
                'submittedDocuments' => $this->submittedDocuments,
                'summary' => $summary,
            ]);
    }
}
