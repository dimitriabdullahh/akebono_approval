<?php

namespace App\Console\Commands;

use App\Models\DocumentRequest;
use App\Models\Supplier;
use App\Mail\RequestLetterMail;
use App\Mail\DocumentsSubmittedMail;
use App\Mail\ApprovalCompletedMail;
use App\Mail\RevisionReminderMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test
                            {type : Email type (request_letter, docs_submitted, approval_completed, revision_reminder)}
                            {--request= : Document request ID (optional, will use first available)}
                            {--to= : Recipient email (optional, default: test@example.com)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send test email to Mailpit for preview';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $type = $this->argument('type');
        $recipient = $this->option('to') ?? 'test@example.com';
        
        // Get document request
        $requestId = $this->option('request');
        $documentRequest = $requestId 
            ? DocumentRequest::with(['supplier', 'requestParts', 'requestDocuments.documentType'])->find($requestId)
            : DocumentRequest::with(['supplier', 'requestParts', 'requestDocuments.documentType'])->first();

        if (!$documentRequest) {
            $this->error('No document request found. Please create one first or specify --request=ID');
            return 1;
        }

        $this->info("Sending test email...");
        $this->line("Type: {$type}");
        $this->line("Request: {$documentRequest->id}");
        $this->line("Supplier: {$documentRequest->supplier->name}");
        $this->line("To: {$recipient}");

        try {
            $portalUrl = route('supplier.dashboard', ['token' => $documentRequest->access_token]);

            $mailable = match($type) {
                'request_letter' => new RequestLetterMail($documentRequest, $portalUrl),
                'docs_submitted' => new DocumentsSubmittedMail($documentRequest),
                'approval_completed' => new ApprovalCompletedMail($documentRequest, $portalUrl),
                'revision_reminder' => new RevisionReminderMail($documentRequest, $portalUrl),
                default => null,
            };

            if (!$mailable) {
                $this->error("Invalid email type. Use: request_letter, docs_submitted, approval_completed, revision_reminder");
                return 1;
            }

            Mail::to($recipient)->send($mailable);

            $this->newLine();
            $this->info('✅ Email sent successfully!');
            $this->line('Open Mailpit to view: http://localhost:8025');
            
            return 0;

        } catch (\Exception $e) {
            $this->error('Failed to send email: ' . $e->getMessage());
            $this->line('Make sure Mailpit is running on port 1025');
            return 1;
        }
    }
}
