<?php

namespace App\Jobs;

use App\Mail\RevisionReminderMail;
use App\Models\DocumentRequest;
use App\Models\NotificationLog;
use App\Models\RequestDocument;
use App\Models\RevisionConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class RevisionReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public DocumentRequest $documentRequest;
    public int $tries = 3;
    public int $backoff = 60;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(DocumentRequest $documentRequest)
    {
        $this->documentRequest = $documentRequest;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $supplier = $this->documentRequest->supplier;

            // Send reminder only if there are documents in REJECTED status
            $revisionDocs = $this->documentRequest->requestDocuments()
                ->where('status', 'REJECTED')
                ->get();

            if ($revisionDocs->count() > 0) {
                // Get first active supplier user
                $supplierUser = $supplier->users()
                    ->where('is_active', true)
                    ->where('role', 'supplier')
                    ->first();

                Mail::to($supplier->pic_email)
                    ->send(new RevisionReminderMail($this->documentRequest, $revisionDocs, $supplierUser));

                NotificationLog::create([
                    'document_request_id' => $this->documentRequest->id,
                    'type' => NotificationLog::TYPE_REVISION_REMINDER,
                    'recipient' => $supplier->pic_email,
                    'subject' => 'Pengingat Revisi Dokumen',
                    'sent_at' => now(),
                    'status' => NotificationLog::STATUS_SENT,
                ]);
            }
        } catch (\Exception $e) {
            NotificationLog::create([
                'document_request_id' => $this->documentRequest->id,
                'type' => NotificationLog::TYPE_REVISION_REMINDER,
                'recipient' => $this->documentRequest->supplier->pic_email,
                'subject' => 'Pengingat Revisi Dokumen',
                'sent_at' => now(),
                'status' => NotificationLog::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);

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
        // Log final failure
        NotificationLog::where('document_request_id', $this->documentRequest->id)
            ->where('type', NotificationLog::TYPE_REVISION_REMINDER)
            ->latest()
            ->first()
            ?->update([
                'error_message' => 'Job failed after attempts: ' . $exception->getMessage(),
            ]);
    }
}
