<?php

namespace App\Jobs;

use App\Mail\RequestLetterMail;
use App\Models\DocumentRequest;
use App\Models\NotificationLog;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendRequestLetterJob implements ShouldQueue
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
            // Fresh load the document request with only necessary relations
            $documentRequest = DocumentRequest::with(['supplier', 'requestParts'])
                ->findOrFail($this->documentRequest->id);

            $supplier = $documentRequest->supplier;

            // Send email with simple link to request page
            Mail::to($supplier->pic_email)
                ->send(new RequestLetterMail($documentRequest));

            // Log success
            NotificationLog::create([
                'document_request_id' => $documentRequest->id,
                'type' => NotificationLog::TYPE_REQUEST_LETTER,
                'recipient' => $supplier->pic_email,
                'subject' => 'Surat Request Dokumen Supplier',
                'sent_at' => now(),
                'status' => NotificationLog::STATUS_SENT,
            ]);
        } catch (\Exception $e) {
            // Log failure
            NotificationLog::create([
                'document_request_id' => $this->documentRequest->id,
                'type' => NotificationLog::TYPE_REQUEST_LETTER,
                'recipient' => $this->documentRequest->supplier->pic_email ?? 'unknown',
                'subject' => 'Surat Request Dokumen Supplier',
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
            ->where('type', NotificationLog::TYPE_REQUEST_LETTER)
            ->latest()
            ->first()
            ?->update([
                'error_message' => 'Job failed after ' . $this->tries . ' attempts: ' . $exception->getMessage(),
            ]);
    }
}
