<?php

namespace App\Jobs;

use App\Mail\BundleSubmissionReminderMail;
use App\Models\DocumentRequest;
use App\Models\DocumentRequestReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BundleSubmissionReminderJob implements ShouldQueue
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

        // Load relasi yang dibutuhkan
        $this->documentRequest->load(['requestParts', 'supplier']);

        // Cek apakah semua parts sudah di-submit
        $allSubmitted = $this->documentRequest->requestParts()
            ->where('submission_status', 'DRAFT')
            ->count() === 0;

        if ($allSubmitted) {
            Log::info("BundleSubmissionReminder: Semua parts sudah submitted, stop reminder", [
                'request_id' => $this->documentRequest->id,
                'model' => $this->documentRequest->model,
            ]);

            $this->reminder->stop();
            return;
        }

        // Get recipient email dan nama
        $recipientEmail = $this->reminder->getCurrentRecipientEmail();
        $recipientName = $this->reminder->getCurrentRecipientName();

        if (!$recipientEmail) {
            Log::warning("BundleSubmissionReminder: Email recipient tidak ditemukan", [
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
                new BundleSubmissionReminderMail(
                    $this->documentRequest,
                    $this->reminder,
                    $this->level,
                    $recipientName
                )
            );

            Log::info("BundleSubmissionReminder: Email sent successfully", [
                'request_id' => $this->documentRequest->id,
                'model' => $this->documentRequest->model,
                'level' => $this->level,
                'recipient' => $recipientEmail,
                'parts_count' => $this->documentRequest->requestParts->count(),
            ]);

            // Update reminder record
            $this->updateReminderForNextLevel();

        } catch (\Exception $e) {
            Log::error("BundleSubmissionReminder: Failed to send email", [
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
        $overallProgress = $this->reminder->getOverallProgressInfo();
        
        $updates = [
            'last_reminder_at' => $now,
        ];

        // Tentukan next level dan delay
        $nextLevel = $this->level;
        $escalationDelay = config('reminder.escalation_delay', 2);

        // Update timestamp berdasarkan level yang baru saja dikirim
        switch ($this->level) {
            case 1:
                $updates['pic_reminder_sent_at'] = $now;
                $updates['initial_reminder_sent_at'] = $now;
                $nextLevel = 2;
                break;
            case 2:
                $updates['supervisor_escalation_sent_at'] = $now;
                $nextLevel = 3;
                break;
            case 3:
                $updates['head_dept_escalation_sent_at'] = $now;
                $nextLevel = 4;
                break;
            case 4:
                $updates['gm_escalation_sent_at'] = $now;
                $nextLevel = 5;
                break;
            case 5:
                $updates['director_escalation_sent_at'] = $now;
                $updates['escalation_status'] = 'ESCALATED_TO_DIRECTOR';
                $updates['next_reminder_at'] = null; // Sudah level maksimal, stop
                $nextLevel = 5; // Tetap di level 5, tidak ada level berikutnya
                break;
        }

        // Set next reminder time dan level untuk reminder berikutnya
        if ($this->level < 5) {
            // Ada level berikutnya, schedule untuk dikirim di masa depan
            $updates['next_reminder_at'] = now()->addMinutes($escalationDelay);
            $updates['reminder_level'] = $nextLevel;
        } else {
            // Sudah level maksimal, tidak ada reminder lagi
            $updates['reminder_level'] = 5;
            $updates['next_reminder_at'] = null;
        }

        $this->reminder->update($updates);

        Log::info("BundleSubmissionReminder: Reminder updated for next level", [
            'request_id' => $this->documentRequest->id,
            'current_level' => $this->level,
            'next_level' => $nextLevel,
            'next_reminder_at' => $updates['next_reminder_at'] ?? null,
            'escalation_status' => $updates['escalation_status'] ?? 'WAITING',
            'total_parts' => $overallProgress['total_parts'],
            'submitted_parts' => $overallProgress['submitted_parts'],
            'draft_parts' => $overallProgress['draft_parts'],
            'uploaded_docs' => $overallProgress['uploaded_documents'],
            'total_docs' => $overallProgress['total_documents'],
        ]);
    }
}
