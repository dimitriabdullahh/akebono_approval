<?php

namespace App\Observers;

use App\Models\DocumentRequest;
use App\Models\DocumentRequestReminder;
use Illuminate\Support\Facades\Log;

class DocumentRequestObserver
{
    /**
     * Handle the DocumentRequest "created" event.
     * Otomatis buat reminder record untuk no submission reminder.
     *
     * @param  \App\Models\DocumentRequest  $documentRequest
     * @return void
     */
    public function created(DocumentRequest $documentRequest)
    {
        // Get initial delay dari config (dalam menit)
        $initialDelay = config('reminder.initial_reminder_delay', 3);

        // Buat reminder record dengan status WAITING
        // reminder_level menunjukkan level yang AKAN dikirim berikutnya
        // Level 1 = PIC (belum dikirim)
        $reminder = DocumentRequestReminder::create([
            'document_request_id' => $documentRequest->id,
            'reminder_level' => 1, // Next level to send: PIC
            'escalation_status' => 'WAITING',
            'next_reminder_at' => now()->addMinutes($initialDelay),
            'document_count_at_last_check' => 0,
        ]);

        Log::info("DocumentRequest created - Auto-created reminder", [
            'request_id' => $documentRequest->id,
            'reminder_id' => $reminder->id,
            'initial_delay_minutes' => $initialDelay,
            'scheduled_at' => $reminder->next_reminder_at,
            'note' => 'Reminder level 1 (PIC) will be sent automatically by scheduler',
        ]);
    }

    /**
     * Handle the DocumentRequest "updated" event.
     *
     * @param  \App\Models\DocumentRequest  $documentRequest
     * @return void
     */
    public function updated(DocumentRequest $documentRequest)
    {
        //
    }

    /**
     * Handle the DocumentRequest "deleted" event.
     *
     * @param  \App\Models\DocumentRequest  $documentRequest
     * @return void
     */
    public function deleted(DocumentRequest $documentRequest)
    {
        //
    }

    /**
     * Handle the DocumentRequest "restored" event.
     *
     * @param  \App\Models\DocumentRequest  $documentRequest
     * @return void
     */
    public function restored(DocumentRequest $documentRequest)
    {
        //
    }

    /**
     * Handle the DocumentRequest "force deleted" event.
     *
     * @param  \App\Models\DocumentRequest  $documentRequest
     * @return void
     */
    public function forceDeleted(DocumentRequest $documentRequest)
    {
        //
    }
}
