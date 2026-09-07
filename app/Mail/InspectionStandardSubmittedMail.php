<?php

namespace App\Mail;

use App\Models\InspectionStandard;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InspectionStandardSubmittedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public InspectionStandard $inspectionStandard;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(InspectionStandard $inspectionStandard)
    {
        $this->inspectionStandard = $inspectionStandard;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $requestDocument = $this->inspectionStandard->requestDocument;
        $documentRequest = $requestDocument->documentRequest;
        
        return $this->markdown('emails.inspection-standard-submitted')
            ->subject('Inspection Standard Submitted: ' . $this->inspectionStandard->part_number . ' - ' . $documentRequest->supplier->name)
            ->with([
                'inspectionStandard' => $this->inspectionStandard,
                'requestDocument' => $requestDocument,
                'documentRequest' => $documentRequest,
                'supplier' => $documentRequest->supplier,
            ]);
    }
}
