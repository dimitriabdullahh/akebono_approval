<?php

namespace App\Jobs;

use App\Mail\DocumentsSubmittedToAkebonoMail;
use App\Models\DocumentRequest;
use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class DocumentsSubmittedToAkebonoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public DocumentRequest $documentRequest;
    public int $submittedCount;
    public array $submittedDocuments;
    public int $tries = 3;
    public int $backoff = 60;

    /**
     * Create a new job instance.
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
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Extract document codes from submitted documents
            $submittedCodes = array_column($this->submittedDocuments, 'code');
            
            // Check which types of documents were submitted
            $hasPSW = in_array('PSW', $submittedCodes);
            $hasPS = in_array('PACKAGING_STANDARD', $submittedCodes);
            
            // Get admins based on what documents were submitted
            $recipientRoles = [];
            
            // QA always gets notified for any document submission
            $recipientRoles[] = 'admin_qa';
            
            // Purchasing gets notified only if PSW or PS was submitted
            if ($hasPSW || $hasPS) {
                $recipientRoles[] = 'admin_purchasing';
            }
            
            // PPC gets notified only if PS was submitted
            if ($hasPS) {
                $recipientRoles[] = 'admin_ppc';
            }
            
            // Get the admins to notify
            $admins = User::whereIn('role', $recipientRoles)
                ->where('is_active', true)
                ->get();

            $documentNames = implode(', ', array_column($this->submittedDocuments, 'name'));

            foreach ($admins as $admin) {
                try {
                    Mail::to($admin->email)
                        ->send(new DocumentsSubmittedToAkebonoMail(
                            $this->documentRequest,
                            $this->submittedCount,
                            $this->submittedDocuments
                        ));

                    NotificationLog::create([
                        'document_request_id' => $this->documentRequest->id,
                        'type' => NotificationLog::TYPE_DOCS_SUBMITTED,
                        'recipient' => $admin->email,
                        'subject' => "Submit: {$this->submittedCount} Dokumen dari {$this->documentRequest->supplier->name}",
                        'sent_at' => now(),
                        'status' => NotificationLog::STATUS_SENT,
                        'metadata' => json_encode([
                            'submitted_count' => $this->submittedCount,
                            'document_names' => $documentNames,
                            'admin_role' => $admin->role,
                        ]),
                    ]);
                } catch (\Exception $e) {
                    NotificationLog::create([
                        'document_request_id' => $this->documentRequest->id,
                        'type' => NotificationLog::TYPE_DOCS_SUBMITTED,
                        'recipient' => $admin->email,
                        'subject' => "Submit: {$this->submittedCount} Dokumen dari {$this->documentRequest->supplier->name}",
                        'sent_at' => now(),
                        'status' => NotificationLog::STATUS_FAILED,
                        'error_message' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        // Catat failure ke notification log
        NotificationLog::create([
            'document_request_id' => $this->documentRequest->id,
            'type' => NotificationLog::TYPE_DOCS_SUBMITTED,
            'recipient' => 'admins@system',
            'subject' => "Submit: {$this->submittedCount} Dokumen (Job Failed)",
            'sent_at' => now(),
            'status' => NotificationLog::STATUS_FAILED,
            'error_message' => 'Job failed after attempts: ' . $exception->getMessage(),
        ]);
    }
}
