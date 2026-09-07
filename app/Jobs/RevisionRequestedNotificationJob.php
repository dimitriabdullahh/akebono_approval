<?php

namespace App\Jobs;

use App\Mail\RevisionRequestedMail;
use App\Models\NotificationLog;
use App\Models\RequestDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class RevisionRequestedNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public RequestDocument $requestDocument;
    public int $tries = 3;
    public int $backoff = 60;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(RequestDocument $requestDocument)
    {
        $this->requestDocument = $requestDocument;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $this->requestDocument->load([
                'documentRequest.supplier.users',
                'documentType',
            ]);

            $supplier = $this->requestDocument->documentRequest->supplier;
            
            // Get first active supplier user
            $supplierUser = $supplier->users()
                ->where('is_active', true)
                ->where('role', 'supplier')
                ->first();

            // Send email to supplier
            Mail::to($supplier->pic_email)
                ->send(new RevisionRequestedMail($this->requestDocument, $supplierUser));

            // Log notification
            NotificationLog::create([
                'document_request_id' => $this->requestDocument->documentRequest->id,
                'type' => NotificationLog::TYPE_REVISION_REQUESTED,
                'recipient' => $supplier->pic_email,
                'subject' => "Revisi Diperlukan: {$this->requestDocument->documentType->name}",
                'sent_at' => now(),
                'status' => NotificationLog::STATUS_SENT,
            ]);
        } catch (\Exception $e) {
            // Log failure
            NotificationLog::create([
                'document_request_id' => $this->requestDocument->documentRequest->id,
                'type' => NotificationLog::TYPE_REVISION_REQUESTED,
                'recipient' => $this->requestDocument->documentRequest->supplier->pic_email,
                'subject' => "Revisi Diperlukan: {$this->requestDocument->documentType->name}",
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
        NotificationLog::where('document_request_id', $this->requestDocument->documentRequest->id)
            ->where('type', NotificationLog::TYPE_REVISION_REQUESTED)
            ->latest()
            ->first()
            ?->update([
                'error_message' => 'Job failed after attempts: ' . $exception->getMessage(),
            ]);
    }
}
