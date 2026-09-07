<?php

namespace App\Console\Commands;

use App\Mail\BundleSubmissionReminderMail;
use App\Models\DocumentRequest;
use App\Models\DocumentRequestReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PreviewBundleReminderEmail extends Command
{
    protected $signature = 'email:preview-bundle-reminder 
                            {requestId : Document Request ID}
                            {--level=1 : Escalation level (1-5)}
                            {--output=preview.html : Output filename}';

    protected $description = 'Preview bundle submission reminder email as HTML file';

    public function handle()
    {
        $requestId = $this->argument('requestId');
        $level = (int) $this->option('level');
        $outputFile = $this->option('output');

        // Validate level
        if ($level < 1 || $level > 5) {
            $this->error('Level must be between 1 and 5');
            return 1;
        }

        // Find document request
        $request = DocumentRequest::with(['supplier', 'requestParts.requestDocuments.documentType'])
            ->find($requestId);

        if (!$request) {
            $this->error("Document Request ID {$requestId} not found");
            return 1;
        }

        // Create temporary reminder for preview
        $reminder = new DocumentRequestReminder([
            'document_request_id' => $request->id,
            'reminder_level' => $level,
            'escalation_status' => 'WAITING',
            'pic_reminder_sent_at' => $level >= 1 ? now()->subMinutes(10) : null,
            'supervisor_escalation_sent_at' => $level >= 2 ? now()->subMinutes(8) : null,
            'head_dept_escalation_sent_at' => $level >= 3 ? now()->subMinutes(6) : null,
            'gm_escalation_sent_at' => $level >= 4 ? now()->subMinutes(4) : null,
            'director_escalation_sent_at' => $level >= 5 ? now()->subMinutes(2) : null,
        ]);

        // Get recipient name
        $recipientName = match($level) {
            1 => $request->supplier->pic_name,
            2 => $request->supplier->supervisor_name,
            3 => $request->supplier->head_dept_name,
            4 => $request->supplier->gm_name,
            5 => $request->supplier->director_name,
            default => 'Unknown',
        };

        // Generate email
        try {
            $mailable = new BundleSubmissionReminderMail(
                $request,
                $reminder,
                $level,
                $recipientName
            );

            $html = $mailable->render();

            // Save to file
            $outputPath = storage_path("app/{$outputFile}");
            File::put($outputPath, $html);

            $this->info("✅ Email preview generated successfully!");
            $this->info("");
            $this->info("📋 Request Info:");
            $this->info("   Model: {$request->model}");
            $this->info("   Supplier: {$request->supplier->name}");
            $this->info("   Total Parts: {$request->requestParts->count()}");
            $this->info("   Level: {$level} ({$this->getLevelName($level)})");
            $this->info("");
            $this->info("📁 File saved to:");
            $this->info("   {$outputPath}");
            $this->info("");
            $this->info("🌐 Open in browser:");
            $this->info("   file:///{$outputPath}");

            // Try to open in default browser (Windows)
            if (PHP_OS_FAMILY === 'Windows') {
                exec("start \"\" \"{$outputPath}\"");
                $this->info("");
                $this->info("🚀 Opening in default browser...");
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("Failed to generate email preview:");
            $this->error($e->getMessage());
            return 1;
        }
    }

    private function getLevelName(int $level): string
    {
        return match($level) {
            1 => 'PIC',
            2 => 'Supervisor',
            3 => 'Head Department',
            4 => 'General Manager',
            5 => 'Director',
            default => 'Unknown',
        };
    }
}
