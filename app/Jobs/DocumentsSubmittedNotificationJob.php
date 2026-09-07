<?php

namespace App\Jobs;

use App\Mail\DocumentsSubmittedMail;
use App\Models\DocumentRequest;
use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class DocumentsSubmittedNotificationJob implements ShouldQueue
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
            // Get all internal admins
            $admins = User::whereIn('role', [
                'admin_qa',
                'admin_purchasing',
                'admin_ppc',
            ])->where('is_active', true)->get();

            foreach ($admins as $admin) {
                try {
                    Mail::to($admin->email)
                        ->send(new DocumentsSubmittedMail($this->documentRequest));

                    NotificationLog::create([
                        'document_request_id' => $this->documentRequest->id,
                        'type' => NotificationLog::TYPE_DOCS_SUBMITTED,
                        'recipient' => $admin->email,
                        'subject' => 'Dokumen Supplier Telah Lengkap',
                        'sent_at' => now(),
                        'status' => NotificationLog::STATUS_SENT,
                    ]);
                } catch (\Exception $e) {
                    NotificationLog::create([
                        'document_request_id' => $this->documentRequest->id,
                        'type' => NotificationLog::TYPE_DOCS_SUBMITTED,
                        'recipient' => $admin->email,
                        'subject' => 'Dokumen Supplier Telah Lengkap',
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
            'subject' => 'Dokumen Supplier Telah Lengkap (Job Failed)',
            'sent_at' => now(),
            'status' => NotificationLog::STATUS_FAILED,
            'error_message' => 'Job failed after attempts: ' . $exception->getMessage(),
        ]);
    }
}
