<?php

namespace App\Console\Commands;

use App\Jobs\DocumentsSubmittedToAkebonoJob;
use App\Models\DocumentRequest;
use Illuminate\Console\Command;

class TestDocumentSubmitNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:document-submit-notification {request_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test notifikasi email ke Akebono saat supplier submit dokumen';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $requestId = $this->argument('request_id');

        if (!$requestId) {
            // Show available requests
            $requests = DocumentRequest::with('supplier', 'requestDocuments.documentType')
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            if ($requests->isEmpty()) {
                $this->error('Tidak ada document request yang tersedia.');
                return 1;
            }

            $this->info('Available Document Requests:');
            $this->line('');

            foreach ($requests as $request) {
                $submittedCount = $request->requestDocuments()
                    ->whereIn('status', ['SUBMITTED', 'REVISE', 'APPROVED'])
                    ->count();

                $this->line(sprintf(
                    'ID: %d | Supplier: %s | Model: %s | Submitted: %d/%d',
                    $request->id,
                    $request->supplier->name,
                    $request->model,
                    $submittedCount,
                    $request->requestDocuments()->count()
                ));
            }

            $this->line('');
            $requestId = $this->ask('Masukkan ID request untuk test email');
        }

        $documentRequest = DocumentRequest::with(['supplier', 'requestDocuments.documentType', 'requestParts'])
            ->find($requestId);

        if (!$documentRequest) {
            $this->error("Document request dengan ID {$requestId} tidak ditemukan.");
            return 1;
        }

        $this->info('Testing email notification untuk:');
        $this->line('Supplier: ' . $documentRequest->supplier->name);
        $this->line('Model: ' . $documentRequest->model);
        $this->line('');

        // Get submitted documents
        $submittedDocs = $documentRequest->requestDocuments()
            ->whereIn('status', ['SUBMITTED', 'REVISE', 'APPROVED'])
            ->with('documentType')
            ->get();

        if ($submittedDocs->isEmpty()) {
            $this->warn('Tidak ada dokumen yang sudah di-submit untuk request ini.');
            
            if (!$this->confirm('Lanjutkan test dengan data dummy?', true)) {
                return 0;
            }

            // Create dummy data for testing
            $submittedDocuments = [
                [
                    'name' => 'Test Document 1',
                    'code' => 'TEST-001',
                    'is_mandatory' => true,
                    'uploaded_at' => now()->format('d M Y H:i'),
                ],
                [
                    'name' => 'Test Document 2',
                    'code' => 'TEST-002',
                    'is_mandatory' => false,
                    'uploaded_at' => now()->format('d M Y H:i'),
                ],
            ];
            $submittedCount = 2;
        } else {
            // Use actual submitted documents
            $submittedDocuments = $submittedDocs->map(function ($doc) {
                return [
                    'name' => $doc->documentType->name,
                    'code' => $doc->documentType->code,
                    'is_mandatory' => $doc->documentType->is_mandatory,
                    'uploaded_at' => $doc->uploaded_at ? $doc->uploaded_at->format('d M Y H:i') : now()->format('d M Y H:i'),
                ];
            })->toArray();
            $submittedCount = $submittedDocs->count();
        }

        $this->info("Mengirim notifikasi untuk {$submittedCount} dokumen...");
        $this->line('');

        // Show documents that will be included in email
        $this->info('Dokumen yang akan di-notifikasi:');
        foreach ($submittedDocuments as $doc) {
            $mandatory = $doc['is_mandatory'] ? '[WAJIB]' : '[OPSIONAL]';
            $this->line("- {$doc['name']} ({$doc['code']}) {$mandatory}");
        }
        $this->line('');

        if ($this->confirm('Kirim test email?', true)) {
            // Dispatch the job
            DocumentsSubmittedToAkebonoJob::dispatch(
                $documentRequest,
                $submittedCount,
                $submittedDocuments
            );

            $this->info('✓ Job berhasil di-dispatch!');
            $this->line('');
            $this->info('Email akan dikirim ke:');
            
            $admins = \App\Models\User::whereIn('role', [
                'admin_qa',
                'admin_purchasing',
                'admin_ppc',
            ])->where('is_active', true)->get();

            foreach ($admins as $admin) {
                $this->line("- {$admin->name} ({$admin->email}) [{$admin->role}]");
            }

            $this->line('');
            $this->info('Cek Mailpit di http://localhost:8025 untuk melihat email.');
            
            return 0;
        }

        $this->info('Test dibatalkan.');
        return 0;
    }
}
