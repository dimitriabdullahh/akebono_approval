<?php

namespace App\Console\Commands;

use App\Models\DocumentRequest;
use App\Models\RequestDocument;
use App\Models\User;
use App\Services\ReviewApprovalService;
use Illuminate\Console\Command;

class DebugDocumentNotInAdmin extends Command
{
    protected $signature = 'debug:documents-not-in-admin {--request-id=} {--admin-id=2}';

    protected $description = 'Debug mengapa dokumen tidak muncul di admin portal untuk approval';

    public function handle()
    {
        $requestId = $this->option('request-id');
        $adminId = $this->option('admin-id');

        $admin = User::find($adminId);
        if (!$admin || !$admin->isInternalAdmin()) {
            $this->error("Admin user dengan ID $adminId tidak ditemukan atau bukan admin");
            return;
        }

        $this->info("=== DEBUG DOKUMEN DI ADMIN PORTAL ===");
        $this->info("Admin: {$admin->name} (Role: {$admin->role})");
        
        // Get admin scope
        $scope = match ($admin->role) {
            'admin_qa' => 'QA',
            'admin_purchasing' => 'PURCHASING',
            'admin_ppc' => 'PPC',
            default => null,
        };
        
        $this->info("Admin Scope: $scope\n");

        // Query dokumen berdasarkan request_id
        if ($requestId) {
            $documents = RequestDocument::whereHas('documentRequest', function ($q) use ($requestId) {
                $q->where('id', $requestId);
            })->get();
        } else {
            $this->info("Menampilkan SEMUA dokumen SUBMITTED di sistem:");
            $documents = RequestDocument::where('status', 'SUBMITTED')->get();
        }

        if ($documents->isEmpty()) {
            $this->warn("Tidak ada dokumen ditemukan");
            return;
        }

        $this->info("Total dokumen SUBMITTED: {$documents->count()}\n");

        // Analysis per dokumen
        $pendingCount = 0;
        $excludedCount = 0;

        foreach ($documents as $doc) {
            $this->line("--- DOKUMEN ID: {$doc->id} ---");
            $this->line("Document Type: {$doc->documentType->name}");
            $this->line("Status: {$doc->status}");
            $this->line("Created At: {$doc->created_at}");
            $this->line("Request ID: {$doc->documentRequest->id}");
            
            if ($doc->documentRequest->requestPart) {
                $this->line("Request Part ID: {$doc->documentRequest->requestPart->id}");
            }

            // Cek 1: Status = SUBMITTED
            if ($doc->status !== 'SUBMITTED') {
                $this->warn("❌ Status bukan SUBMITTED (Status: {$doc->status})");
                $excludedCount++;
                $this->line("");
                continue;
            } else {
                $this->info("✓ Status = SUBMITTED");
            }

            // Cek 2: Document Type Scope
            $docScope = json_decode($doc->documentType->scope ?? '[]', true) ?? [];
            $this->line("Document Type Scope: " . json_encode($docScope));
            
            if (!in_array($scope, $docScope)) {
                $this->warn("❌ Admin scope '{$scope}' TIDAK ada di document_type.scope");
                $this->warn("   Document type ini hanya untuk scope: " . implode(', ', $docScope));
                $excludedCount++;
                $this->line("");
                continue;
            } else {
                $this->info("✓ Document type scope '{$scope}' sesuai");
            }

            // Cek 3: Sudah di-approve oleh scope ini?
            $approval = $doc->approvals()
                ->where('scope', $scope)
                ->where('status', 'APPROVED')
                ->first();

            if ($approval) {
                $this->warn("❌ Sudah di-APPROVE oleh {$scope}");
                $this->warn("   Approved at: {$approval->created_at}");
                $this->warn("   Approved by: {$approval->user->name}");
                $excludedCount++;
                $this->line("");
                continue;
            } else {
                $this->info("✓ Belum di-approve oleh scope '{$scope}'");
            }

            // Cek approval history
            $allApprovals = $doc->approvals()->get();
            if ($allApprovals->isNotEmpty()) {
                $this->line("Approval History:");
                foreach ($allApprovals as $apr) {
                    $this->line("  - {$apr->scope}: {$apr->status} (by {$apr->user->name} at {$apr->created_at})");
                }
            }

            // Dokumen ini SEHARUSNYA muncul di admin portal
            $this->info("✓✓✓ SEHARUSNYA MUNCUL DI ADMIN PORTAL");
            $pendingCount++;
            $this->line("");
        }

        // Summary
        $this->info("=== SUMMARY ===");
        $this->info("✓ Dokumen yang SEHARUSNYA muncul: $pendingCount");
        $this->warn("❌ Dokumen yang EXCLUDED: $excludedCount");

        // Verify dengan actual query
        $this->info("\n=== VERIFIKASI DENGAN ACTUAL QUERY ===");
        $service = new ReviewApprovalService();
        $actualPending = $service->getPendingDocumentsForReview($adminId);
        $this->info("Dokumen di pending list admin: {$actualPending->count()}");

        if ($actualPending->count() > 0) {
            $this->line("Dokumen di pending list:");
            foreach ($actualPending as $doc) {
                $this->line("- ID {$doc->id}: {$doc->documentType->name}");
            }
        }
    }
}
