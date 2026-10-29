<?php

namespace App\Console\Commands;

use App\Models\PackagingStandard;
use Illuminate\Console\Command;

class FillPackagingStandardApprovalNames extends Command
{
    protected $signature = 'ps:fill-approval-names {ps_id} {--prepared=} {--checked=} {--approved=}';
    protected $description = 'Isi nama approval untuk Packaging Standard yang sudah ada';

    public function handle()
    {
        $psId = $this->argument('ps_id');
        $ps = PackagingStandard::find($psId);
        
        if (!$ps) {
            $this->error("Packaging Standard ID {$psId} tidak ditemukan!");
            return 1;
        }
        
        $this->info("=== PACKAGING STANDARD #{$psId} ===");
        $this->line("Part No: {$ps->part_no}");
        $this->line("Part Name: {$ps->part_name}");
        $this->newLine();
        
        // Ambil nama dari option atau tanya user
        $preparedName = $this->option('prepared') ?: $this->ask('Prepared By Name');
        $checkedName = $this->option('checked') ?: $this->ask('Checked By Name');
        $approvedName = $this->option('approved') ?: $this->ask('Approved By Name');
        
        if (empty($preparedName) && empty($checkedName) && empty($approvedName)) {
            $this->error('Tidak ada nama yang diisi!');
            return 1;
        }
        
        // Update data
        $updateData = [];
        
        if ($preparedName) {
            $updateData['prepared_by_name'] = $preparedName;
            $updateData['prepared_by_date'] = $updateData['prepared_by_date'] ?? now();
            
            // Generate QR code
            $preparedQr = \App\Services\QrCodeService::generateApprovalQR($preparedName, null, null);
            if ($preparedQr) {
                $updateData['prepared_qr_data'] = $preparedQr;
            }
        }
        
        if ($checkedName) {
            $updateData['checked_by_name'] = $checkedName;
            $updateData['checked_by_date'] = $updateData['checked_by_date'] ?? now();
            
            // Generate QR code
            $checkedQr = \App\Services\QrCodeService::generateApprovalQR($checkedName, null, null);
            if ($checkedQr) {
                $updateData['checked_qr_data'] = $checkedQr;
            }
        }
        
        if ($approvedName) {
            $updateData['approved_by_name'] = $approvedName;
            $updateData['approved_by_date'] = $updateData['approved_by_date'] ?? now();
            
            // Generate QR code
            $approvedQr = \App\Services\QrCodeService::generateApprovalQR($approvedName, null, null);
            if ($approvedQr) {
                $updateData['approved_qr_data'] = $approvedQr;
            }
        }
        
        $ps->update($updateData);
        
        $this->newLine();
        $this->info('✅ Data berhasil diupdate!');
        $this->newLine();
        
        $this->info('=== DATA APPROVAL ===');
        $this->line("Prepared By: {$ps->prepared_by_name}");
        $this->line("Checked By: {$ps->checked_by_name}");
        $this->line("Approved By: {$ps->approved_by_name}");
        $this->newLine();
        
        $this->info('=== QR CODE STATUS ===');
        $this->line("Prepared QR: " . ($ps->prepared_qr_data ? '✓ Generated' : '✗ Not generated'));
        $this->line("Checked QR: " . ($ps->checked_qr_data ? '✓ Generated' : '✗ Not generated'));
        $this->line("Approved QR: " . ($ps->approved_qr_data ? '✓ Generated' : '✗ Not generated'));
        $this->newLine();
        
        $this->info('Sekarang Anda bisa generate PDF dan nama akan muncul!');
        
        return 0;
    }
}
