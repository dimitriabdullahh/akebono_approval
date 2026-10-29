<?php

namespace App\Mail;

use App\Models\ApprovalToken;
use App\Models\DocumentApproval;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApprovalRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public DocumentApproval $approval;
    public ApprovalToken $token;
    public array $documentData;
    public string $documentType;
    public string $approvalUrl;

    /**
     * Create a new message instance.
     *
     * @param DocumentApproval $approval
     * @param ApprovalToken $token
     */
    public function __construct(DocumentApproval $approval, ApprovalToken $token)
    {
        $this->approval = $approval;
        $this->token = $token;
        
        // Load document data
        $requestDocument = $approval->requestDocument;
        $requestDocument->load([
            'documentRequest.supplier',
            'documentType',
            'requestPart',
            'partSubmissionWarrant',
            'packagingStandard',
        ]);
        
        $this->documentType = $requestDocument->documentType->code;
        
        // Prepare document data based on type
        $this->documentData = $this->prepareDocumentData($requestDocument);
        
        // Generate approval URL
        $this->approvalUrl = $token->getApprovalUrl();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = sprintf(
            '[AKB APPROVAL] %s Approval Required - %s',
            $this->documentType === 'PSW' ? 'PSW' : 'PS',
            $this->documentData['part_number'] ?? 'Document'
        );
        
        $viewName = $this->documentType === 'PSW' 
            ? 'emails.approval.psw-request'
            : 'emails.approval.ps-request';
        
        return $this->subject($subject)
            ->view($viewName)
            ->with([
                'approval' => $this->approval,
                'token' => $this->token,
                'documentData' => $this->documentData,
                'approvalUrl' => $this->approvalUrl,
                'expiresAt' => $this->token->expires_at,
            ]);
    }

    /**
     * Prepare document data for email
     *
     * @param \App\Models\RequestDocument $requestDocument
     * @return array
     */
    protected function prepareDocumentData($requestDocument): array
    {
        $data = [
            'document_number' => $requestDocument->id,
            'submission_date' => $requestDocument->submitted_at 
                ? $requestDocument->submitted_at->format('d F Y, H:i') 
                : '-',
            'supplier_name' => $requestDocument->documentRequest->supplier->name ?? '-',
            'model' => $requestDocument->documentRequest->model ?? '-',
            'due_date' => $requestDocument->documentRequest->due_date 
                ? $requestDocument->documentRequest->due_date->format('d F Y') 
                : '-',
        ];
        
        // Add part information
        if ($requestDocument->requestPart) {
            $data['part_name'] = $requestDocument->requestPart->part_name;
            $data['part_number'] = $requestDocument->requestPart->part_number;
        }
        
        // Add PSW specific data
        if ($this->documentType === 'PSW' && $requestDocument->partSubmissionWarrant) {
            $psw = $requestDocument->partSubmissionWarrant;
            $data['psw_part_name'] = $psw->part_name;
            $data['psw_part_number'] = $psw->part_number;
            $data['psw_submitted_at'] = $psw->submitted_at 
                ? $psw->submitted_at->format('d F Y, H:i') 
                : '-';
        }
        
        // Add PS specific data
        if ($this->documentType === 'PACKAGING_STANDARD' && $requestDocument->packagingStandard) {
            $ps = $requestDocument->packagingStandard;
            $data['ps_part_name'] = $ps->part_name;
            $data['ps_part_number'] = $ps->part_no;
            $data['ps_document_no'] = $ps->document_no;
            $data['ps_packaging_type'] = $ps->packaging_type;
            $data['ps_submitted_at'] = $ps->submitted_at 
                ? $ps->submitted_at->format('d F Y, H:i') 
                : '-';
        }
        
        return $data;
    }
}
