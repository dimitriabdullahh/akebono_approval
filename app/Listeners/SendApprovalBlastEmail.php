<?php

namespace App\Listeners;

use App\Events\DocumentSubmitted;
use App\Mail\ApprovalNotificationMail;
use App\Models\ApprovalBlastToken;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendApprovalBlastEmail // implements ShouldQueue - DISABLED FOR DEBUGGING
{
    // use InteractsWithQueue; - DISABLED FOR DEBUGGING

    // public $tries = 3; - DISABLED FOR DEBUGGING
    // public $timeout = 120; - DISABLED FOR DEBUGGING

    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle(DocumentSubmitted $event): void
    {
        try {
            \Log::info('[ApprovalBlast] Starting handle method');
            
            $requestDocument = $event->requestDocument;
            $documentType = $requestDocument->documentType->code ?? 'UNKNOWN';

            \Log::info('[ApprovalBlast] Got document info', [
                'doc_id' => $requestDocument->id,
                'type' => $documentType
            ]);

            // Get divisions yang perlu approve dari config
            $divisions = config('approval_blast.document_divisions.' . $documentType, []);

            // Jika tidak ada divisi yang ditentukan, skip
            if (empty($divisions)) {
                Log::info('No divisions configured for document type: ' . $documentType);
                return;
            }

            Log::info('Starting approval blast', [
                'request_document_id' => $requestDocument->id,
                'document_type' => $documentType,
                'divisions' => $divisions,
            ]);

            // Untuk setiap divisi, cari user dan kirim email
            foreach ($divisions as $division) {
                \Log::info('[ApprovalBlast] Processing division: ' . $division);
                $this->sendToDivision($requestDocument, $division, $documentType);
            }
            
            \Log::info('[ApprovalBlast] Completed successfully');
        } catch (\Throwable $e) {
            \Log::error('[ApprovalBlast] Fatal error in handle', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Send approval emails to all users in a division
     */
    private function sendToDivision(\App\Models\RequestDocument $requestDocument, string $division, string $documentType): void
    {
        // Get users untuk division ini
        $role = config('approval_blast.division_roles.' . strtolower($division));
        
        if (!$role) {
            Log::warning('No role mapping found for division: ' . $division);
            return;
        }

        $users = User::where('role', $role)
            ->where('is_active', true)
            ->whereNotNull('email')
            ->get();

        if ($users->isEmpty()) {
            Log::warning('No active users found for division: ' . $division);
            return;
        }

        // Send email ke setiap user
        foreach ($users as $user) {
            try {
                // Check if token sudah pernah dibuat untuk dokumen ini dan division ini
                $existingToken = ApprovalBlastToken::forDocument($requestDocument->id)
                    ->forDivision($division)
                    ->whereNull('used_at')
                    ->whereNull('invalidated_at')
                    ->first();

                // Jika sudah ada token valid, tidak perlu buat yang baru
                if ($existingToken && !$existingToken->isExpired()) {
                    Log::info('Token already exists for document', [
                        'request_document_id' => $requestDocument->id,
                        'division' => $division,
                        'user_id' => $user->id,
                    ]);
                    // Tapi tetap kirim email dengan token yang ada
                    $token = $existingToken;
                } else {
                    // Invalidate token lama jika ada
                    ApprovalBlastToken::invalidateForDocument($requestDocument->id);

                    // Generate token baru
                    $token = ApprovalBlastToken::generateForApproval(
                        $requestDocument,
                        $user,
                        $division
                    );

                    Log::info('Generated approval token', [
                        'request_document_id' => $requestDocument->id,
                        'user_id' => $user->id,
                        'division' => $division,
                        'token_id' => $token->id,
                    ]);
                }

                // Kirim email
                Mail::to($user->email)->send(
                    new ApprovalNotificationMail(
                        $requestDocument,
                        $user,
                        $division,
                        $token->getPlainToken()
                    )
                );

                Log::info('Approval blast email sent', [
                    'request_document_id' => $requestDocument->id,
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'division' => $division,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send approval blast email', [
                    'request_document_id' => $requestDocument->id,
                    'user_id' => $user->id,
                    'division' => $division,
                    'error' => $e->getMessage(),
                ]);

                // Jangan throw exception - kegagalan email tidak boleh menggagalkan proses
                $this->failed($event, $e);
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(DocumentSubmitted $event, \Throwable $exception): void
    {
        Log::error('SendApprovalBlastEmail failed', [
            'request_document_id' => $event->requestDocument->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
