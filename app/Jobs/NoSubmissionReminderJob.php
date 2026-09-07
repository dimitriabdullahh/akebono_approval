<?php

namespace App\Jobs;

use App\Mail\NoSubmissionReminderMail;
use App\Models\DocumentRequest;
use App\Models\DocumentRequestReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NoSubmissionReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public DocumentRequest $documentRequest;
    public DocumentRequestReminder $reminder;
    public int $level;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(DocumentRequest $documentRequest, DocumentRequestReminder $reminder, int $level)
    {
        $this->documentRequest = $documentRequest;
        $this->reminder = $reminder;
        $this->level = $level;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Reload dari database untuk data terbaru
        $this->reminder->refresh();
        $this->documentRequest->refresh();

        // Cek apakah sudah ada dokumen yang dikirim
        $documentCount = $this->documentRequest->requestDocuments()
            ->whereNotNull('file_path')
            ->whereIn('status', ['SUBMITTED', 'APPROVED', 'REVISE'])
            ->count();

        // Jika sudah ada dokumen, stop reminder
        if ($documentCount > 0) {
            Log::info("NoSubmissionReminder: Dokumen sudah dikirim, stop reminder", [
                'request_id' => $this->documentRequest->id,
                'document_count' => $documentCount,
            ]);

            $this->reminder->stop();
            return;
        }

        // Get recipient email dan nama
        $recipientEmail = $this->reminder->getCurrentRecipientEmail();
        $recipientName = $this->reminder->getCurrentRecipientName();

        if (!$recipientEmail) {
            Log::warning("NoSubmissionReminder: Email recipient tidak ditemukan", [
                'request_id' => $this->documentRequest->id,
                'level' => $this->level,
                'supplier_id' => $this->documentRequest->supplier_id,
            ]);
            
            // Tandai reminder sebagai error tapi tetap lanjutkan ke level berikutnya
            $this->updateReminderForNextLevel();
            return;
        }

        // Kirim email
        try {
            Mail::to($recipientEmail)->send(
                new NoSubmissionReminderMail(
                    $this->documentRequest,
                    $this->reminder,
                    $this->level,
                    $recipientName
                )
            );

            Log::info("NoSubmissionReminder: Email sent successfully", [
                'request_id' => $this->documentRequest->id,
                'level' => $this->level,
                'recipient' => $recipientEmail,
            ]);

            // Update reminder record
            $this->updateReminderForNextLevel();

        } catch (\Exception $e) {
            Log::error("NoSubmissionReminder: Failed to send email", [
                'request_id' => $this->documentRequest->id,
                'level' => $this->level,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Update reminder untuk level berikutnya
     */
    private function updateReminderForNextLevel()
    {
        $now = now();
        $updates = [
            'last_reminder_at' => $now,
            'document_count_at_last_check' => 0,
        ];

        // Update timestamp berdasarkan level
        switch ($this->level) {
            case 1:
                $updates['pic_reminder_sent_at'] = $now;
                $updates['initial_reminder_sent_at'] = $now;
                break;
            case 2:
                $updates['supervisor_escalation_sent_at'] = $now;
                break;
            case 3:
                $updates['head_dept_escalation_sent_at'] = $now;
                break;
            case 4:
                $updates['gm_escalation_sent_at'] = $now;
                break;
            case 5:
                $updates['director_escalation_sent_at'] = $now;
                $updates['escalation_status'] = 'ESCALATED_TO_DIRECTOR';
                $updates['next_reminder_at'] = null; // Sudah level maksimal, stop
                break;
        }

        // Set next reminder time jika belum level maksimal
        if ($this->level < 5) {
            $escalationDelay = config('reminder.escalation_delay', 2);
            $updates['next_reminder_at'] = now()->addMinutes($escalationDelay);
            $updates['reminder_level'] = $this->level + 1; // Increment ke level berikutnya
        } else {
            $updates['reminder_level'] = 5; // Tetap di level 5
        }

        $this->reminder->update($updates);

        Log::info("NoSubmissionReminder: Reminder updated for next level", [
            'request_id' => $this->documentRequest->id,
            'current_level' => $this->level,
            'next_level' => $updates['reminder_level'] ?? $this->level,
            'next_reminder_at' => $updates['next_reminder_at'] ?? null,
        ]);
    }
}
