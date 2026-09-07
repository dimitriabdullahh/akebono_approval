<?php

namespace App\Console\Commands;

use App\Mail\RevisionRequestedMail;
use App\Models\RequestDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestRevisionRequestedEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:revision-requested-email {document_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test revision requested notification email';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $documentId = $this->argument('document_id');

        if ($documentId) {
            $requestDocument = RequestDocument::with([
                'documentRequest.supplier',
                'documentRequest.requestParts',
                'documentType'
            ])->find($documentId);

            if (!$requestDocument) {
                $this->error("Document with ID {$documentId} not found!");
                return 1;
            }
        } else {
            // Find a document in REVISE status or create test data
            $requestDocument = RequestDocument::with([
                'documentRequest.supplier',
                'documentRequest.requestParts',
                'documentType'
            ])
                ->where('status', 'REVISE')
                ->first();

            if (!$requestDocument) {
                $this->error('No document with REVISE status found!');
                $this->info('Please specify a document ID or create a document with REVISE status first.');
                return 1;
            }
        }

        $this->info('Testing Revision Requested Email...');
        $this->info('Document ID: ' . $requestDocument->id);
        $this->info('Document Type: ' . $requestDocument->documentType->name);
        $this->info('Status: ' . $requestDocument->status);
        $this->info('Supplier: ' . $requestDocument->documentRequest->supplier->name);
        $this->info('PIC Email: ' . $requestDocument->documentRequest->supplier->pic_email);
        
        if ($requestDocument->revision_note) {
            $this->info('Revision Note: ' . $requestDocument->revision_note);
        }
        
        if ($requestDocument->revision_deadline_at) {
            $this->info('Deadline: ' . $requestDocument->revision_deadline_at->format('d F Y, H:i'));
        }

        $this->newLine();

        if (!$this->confirm('Send test email to ' . $requestDocument->documentRequest->supplier->pic_email . '?', true)) {
            $this->info('Test cancelled.');
            return 0;
        }

        try {
            $portalUrl = route('supplier.dashboard', [
                'token' => $requestDocument->documentRequest->access_token
            ]);

            Mail::to($requestDocument->documentRequest->supplier->pic_email)
                ->send(new RevisionRequestedMail($requestDocument, $portalUrl));

            $this->newLine();
            $this->info('✅ Email sent successfully!');
            $this->info('Check Mailpit at: http://localhost:8025');
            
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Failed to send email: ' . $e->getMessage());
            return 1;
        }
    }
}
