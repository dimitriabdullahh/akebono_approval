<?php

namespace App\Console\Commands;

use App\Models\DocumentType;
use Illuminate\Console\Command;

class RefreshDocumentTypes extends Command
{
    protected $signature = 'document-types:refresh';
    protected $description = 'Refresh document types to match Implementation Document specification';

    public function handle()
    {
        $this->info('Refreshing Document Types according to Implementation Document...');

        // First, clean up old document types that are not in the specification
        $oldCodes = ['COA', 'MSDS', 'QC', 'QCP', 'PIS', 'MILL_SHEET', 'ROHS_COMPLIANCE', 
                     'PACKAGING_SPEC', 'PROD_TRIAL', 'TOOLING_PLAN', 'CAPACITY_VER', 
                     'DELIVERY_AGREE', 'PRICING_SHEET', 'SQAA', 'SUBSUPPLIER_CONTROL', 'SPECIAL_PROCESS'];
        $removedCount = 0;
        
        foreach ($oldCodes as $code) {
            $old = DocumentType::where('code', $code)->first();
            if ($old) {
                $old->delete();
                $this->line("🗑️ Removed old document type: {$code}");
                $removedCount++;
            }
        }

        if ($removedCount > 0) {
            $this->info("Cleaned up {$removedCount} old document types.");
        }

        // Define all document types according to Implementation Document Section 14.2
        $documentTypes = [
            // 14 Mandatory Documents
            [
                'name' => 'Process Flow Diagram (PFD)',
                'code' => 'PFD',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Diagram alur proses produksi'
            ],
            [
                'name' => 'Process Failure Mode dan Effect Analysis (FMEA)',
                'code' => 'FMEA',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Analisis mode kegagalan proses dan dampaknya'
            ],
            [
                'name' => 'Quality Control Process Chart (QCPC)',
                'code' => 'QCPC',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Chart proses kontrol kualitas'
            ],
            [
                'name' => 'Measurement System Analysis (MSA)',
                'code' => 'MSA',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Analisis sistem pengukuran'
            ],
            [
                'name' => 'Inspection Standard (IS)',
                'code' => 'IS',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Standar inspeksi'
            ],
            [
                'name' => 'Initial Sample Inspection Report (ISIR)',
                'code' => 'ISIR',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Laporan inspeksi sampel awal'
            ],
            [
                'name' => 'X-R Control Chart (CP-CK)',
                'code' => 'XR_CONTROL_CHART',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Chart kontrol X-R (CP-CK)'
            ],
            [
                'name' => 'Appearance Approval Report (AAR)',
                'code' => 'AAR',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Laporan persetujuan tampilan'
            ],
            [
                'name' => 'Limit Sample Tag',
                'code' => 'LIMIT_SAMPLE_TAG',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Tag sampel batas'
            ],
            [
                'name' => 'Packaging Standard (PS)',
                'code' => 'PACKAGING_STANDARD',
                'is_mandatory' => true,
                'scope' => ['PURCHASING', 'PPC', 'QA'],
                'description' => 'Standar kemasan'
            ],
            [
                'name' => 'Part Submission Warrant (PSW)',
                'code' => 'PSW',
                'is_mandatory' => true,
                'scope' => ['PURCHASING', 'QA'],
                'description' => 'Surat jaminan pengajuan part'
            ],
            [
                'name' => 'Mill Sheet Material (Dimensi, Material, Colour-check, dll.)',
                'code' => 'MILL_SHEET_MATERIAL',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Lembar material mill dengan dimensi, material, colour-check, dll.'
            ],
            [
                'name' => 'Certificate of Calibration',
                'code' => 'CERTIFICATE_CALIBRATION',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Sertifikat kalibrasi'
            ],
            [
                'name' => 'Drawing Product',
                'code' => 'DRAWING_PRODUCT',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Gambar produk'
            ],

            // 2 Optional Documents
            [
                'name' => 'SOC Free',
                'code' => 'SOC_FREE',
                'is_mandatory' => false,
                'scope' => ['QA'],
                'description' => 'Sertifikat SOC Free (Optional)'
            ],
            [
                'name' => 'IMDS (International Material Data System)',
                'code' => 'IMDS',
                'is_mandatory' => false,
                'scope' => ['QA'],
                'description' => 'International Material Data System (Optional)'
            ],
        ];

        $this->info('Updating/Creating document types...');

        foreach ($documentTypes as $docType) {
            $existing = DocumentType::where('code', $docType['code'])->first();
            
            if ($existing) {
                $existing->update($docType);
                $this->line("✅ Updated: {$docType['name']} ({$docType['code']})");
            } else {
                DocumentType::create($docType);
                $this->line("➕ Created: {$docType['name']} ({$docType['code']})");
            }
        }

        $this->info('');
        $this->info('=== DOCUMENT TYPES SUMMARY ===');
        
        $mandatoryCount = DocumentType::where('is_mandatory', true)->count();
        $optionalCount = DocumentType::where('is_mandatory', false)->count();
        $totalCount = DocumentType::count();

        $this->line("📋 Mandatory Documents: {$mandatoryCount}");
        $this->line("📄 Optional Documents: {$optionalCount}");
        $this->line("📊 Total Documents: {$totalCount}");

        $this->info('');
        $this->info('=== APPROVAL SCOPE DISTRIBUTION ===');
        
        $qaCount = DocumentType::whereJsonContains('scope', 'QA')->count();
        $purchasingCount = DocumentType::whereJsonContains('scope', 'PURCHASING')->count();
        $ppcCount = DocumentType::whereJsonContains('scope', 'PPC')->count();

        $this->line("🔍 QA Scope: {$qaCount} documents");
        $this->line("💰 PURCHASING Scope: {$purchasingCount} documents");
        $this->line("🏭 PPC Scope: {$ppcCount} documents");

        $this->newLine();
        $this->info('✅ Document types have been successfully refreshed according to Implementation Document!');

        return Command::SUCCESS;
    }
}