<?php

namespace App\Jobs;

use App\Mail\ApprovalCompletedMail;
use App\Models\DocumentRequest;
use App\Models\NotificationLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ApprovalCompletedNotificationJob implements ShouldQueue
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
            $portalUrl = route('supplier.dashboard', ['token' => $this->documentRequest->access_token]);

            // Send to supplier
            Mail::to($supplier->pic_email)
                ->send(new ApprovalCompletedMail($this->documentRequest, $portalUrl));

            NotificationLog::create([
                'document_request_id' => $this->documentRequest->id,
                'type' => NotificationLog::TYPE_APPROVAL_COMPLETED,
                'recipient' => $supplier->pic_email,
                'subject' => 'Approval Selesai - Semua Dokumen Disetujui',
                'sent_at' => now(),
                'status' => NotificationLog::STATUS_SENT,
            ]);

            // Also send to Admin QA (creator) and admins
            $creator = $this->documentRequest->creator;
            if ($creator && $creator->email) {
                Mail::to($creator->email)
                    ->send(new ApprovalCompletedMail($this->documentRequest, $portalUrl));

                NotificationLog::create([
                    'document_request_id' => $this->documentRequest->id,
                    'type' => NotificationLog::TYPE_APPROVAL_COMPLETED,
                    'recipient' => $creator->email,
                    'subject' => 'Approval Selesai - Semua Dokumen Disetujui',
                    'sent_at' => now(),
                    'status' => NotificationLog::STATUS_SENT,
                ]);
            }
        } catch (\Exception $e) {
            NotificationLog::create([
                'document_request_id' => $this->documentRequest->id,
                'type' => NotificationLog::TYPE_APPROVAL_COMPLETED,
                'recipient' => $this->documentRequest->supplier->pic_email,
                'subject' => 'Approval Selesai - Semua Dokumen Disetujui',
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
            ->where('type', NotificationLog::TYPE_APPROVAL_COMPLETED)
            ->latest()
            ->first()
            ?->update([
                'error_message' => 'Job failed after attempts: ' . $exception->getMessage(),
            ]);
    }
}
