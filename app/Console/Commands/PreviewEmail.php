<?php

namespace App\Console\Commands;

use App\Mail\ApprovalCompletedMail;
use App\Mail\DocumentsSubmittedMail;
use App\Mail\RequestLetterMail;
use App\Mail\RevisionReminderMail;
use App\Models\DocumentRequest;
use App\Models\Supplier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class PreviewEmail extends Command
{
    protected $signature = 'email:preview 
                            {type : Type of email (request-letter, documents-submitted, approval-completed, revision-reminder)}
                            {--request-id= : Document Request ID for preview}
                            {--save : Save HTML to file instead of displaying}';

    protected $description = 'Preview email templates with sample or real data';

    public function handle()
    {
        $type = $this->argument('type');
        $requestId = $this->option('request-id');
        $save = $this->option('save');

        try {
            $mailable = $this->getMailable($type, $requestId);
            
            if (!$mailable) {
                $this->error('Invalid email type or request ID');
                return 1;
            }

            $html = $mailable->render();

            if ($save) {
                $filename = storage_path("app/email-preview-{$type}-" . now()->format('Y-m-d-His') . ".html");
                file_put_contents($filename, $html);
                $this->info("Email preview saved to: {$filename}");
                $this->info("Open this file in your browser to see the email.");
            } else {
                $this->line('');
                $this->line('=== EMAIL PREVIEW ===');
                $this->line('');
                $this->line($html);
                $this->line('');
                $this->info('Tip: Use --save option to save HTML file and open in browser for better preview');
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('Error generating preview: ' . $e->getMessage());
            return 1;
        }
    }

    protected function getMailable($type, $requestId)
    {
        if ($requestId) {
            $request = DocumentRequest::with(['supplier', 'parts.documents.type', 'parts.documents.history'])
                ->find($requestId);
            
            if (!$request) {
                $this->error("Request ID {$requestId} not found");
                return null;
            }
        } else {
            // Create dummy data for preview
            $request = $this->createDummyRequest();
        }

        switch ($type) {
            case 'request-letter':
                return new RequestLetterMail($request, 'https://example.com/portal/token123');
            
            case 'documents-submitted':
                return new DocumentsSubmittedMail($request);
            
            case 'approval-completed':
                return new ApprovalCompletedMail($request);
            
            case 'revision-reminder':
                $revisionDays = 3;
                return new RevisionReminderMail($request, $revisionDays);
            
            default:
                $this->error("Unknown email type: {$type}");
                $this->info("Available types: request-letter, documents-submitted, approval-completed, revision-reminder");
                return null;
        }
    }

    protected function createDummyRequest()
    {
        // Create a mock request object for preview
        $supplier = new Supplier([
            'code' => 'SUP001',
            'name' => 'PT Example Supplier Indonesia',
            'email' => 'supplier@example.com',
        ]);

        $request = new DocumentRequest([
            'id' => 999,
            'request_number' => 'REQ-2024-001',
            'supplier_code' => 'SUP001',
            'status' => 'pending_supplier',
            'created_at' => now()->subDays(2),
            'updated_at' => now(),
        ]);

        $request->setRelation('supplier', $supplier);

        // You can add mock parts and documents if needed
        $request->setRelation('parts', collect());

        return $request;
    }
}
