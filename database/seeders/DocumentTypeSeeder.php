<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $documents = [
            // ===== FORM-BASED DOCUMENTS (PRIORITY) =====
            [
                'name' => 'Inspection Standard (IS)',
                'code' => 'IS',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Standar inspeksi',
                'order' => 1,
            ],
            [
                'name' => 'Packaging Standard (PS)',
                'code' => 'PACKAGING_STANDARD',
                'is_mandatory' => true,
                'scope' => ['PURCHASING', 'PPC', 'QA'],
                'description' => 'Standar kemasan',
                'order' => 2,
            ],
            [
                'name' => 'Part Submission Warrant (PSW)',
                'code' => 'PSW',
                'is_mandatory' => true,
                'scope' => ['PURCHASING', 'QA'],
                'description' => 'Surat jaminan pengajuan part',
                'order' => 3,
            ],
            [
                'name' => 'Measurement System Analysis (MSA)',
                'code' => 'MSA',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Analisis sistem pengukuran',
                'order' => 4,
            ],

            // ===== UPLOAD-BASED DOCUMENTS (MANDATORY) =====
            [
                'name' => 'Process Flow Diagram (PFD)',
                'code' => 'PFD',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Diagram alur proses produksi',
                'order' => 5,
            ],
            [
                'name' => 'Process Failure Mode dan Effect Analysis (FMEA)',
                'code' => 'FMEA',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Analisis mode kegagalan proses dan dampaknya',
                'order' => 6,
            ],
            [
                'name' => 'Quality Control Process Chart (QCPC)',
                'code' => 'QCPC',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Chart proses kontrol kualitas',
                'order' => 7,
            ],
            [
                'name' => 'Initial Sample Inspection Report (ISIR)',
                'code' => 'ISIR',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Laporan inspeksi sampel awal',
                'order' => 8,
            ],
            [
                'name' => 'Appearance Approval Report (AAR)',
                'code' => 'AAR',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Laporan persetujuan tampilan',
                'order' => 9,
            ],
            [
                'name' => 'Limit Sample Tag',
                'code' => 'LIMIT_SAMPLE_TAG',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Tag sampel batas',
                'order' => 10,
            ],
            [
                'name' => 'Mill Sheet Material (Dimensi, Material, Colour-check, dll.)',
                'code' => 'MILL_SHEET_MATERIAL',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Lembar material mill dengan dimensi, material, colour-check, dll.',
                'order' => 11,
            ],
            [
                'name' => 'Certificate of Calibration',
                'code' => 'CERTIFICATE_CALIBRATION',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Sertifikat kalibrasi',
                'order' => 12,
            ],
            [
                'name' => 'Drawing Product',
                'code' => 'DRAWING_PRODUCT',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Gambar produk',
                'order' => 13,
            ],

            // ===== OPTIONAL DOCUMENTS =====
            [
                'name' => 'SOC Free',
                'code' => 'SOC_FREE',
                'is_mandatory' => false,
                'scope' => ['QA'],
                'description' => 'Sertifikat SOC Free (Optional)',
                'order' => 14,
            ],
            [
                'name' => 'IMDS (International Material Data System)',
                'code' => 'IMDS',
                'is_mandatory' => false,
                'scope' => ['QA'],
                'description' => 'International Material Data System (Optional)',
                'order' => 15,
            ],

            // ===== COMING SOON (FORM-BASED) =====
            [
                'name' => 'Capability Data N30 (CP,CPK)',
                'code' => 'XR_CONTROL_CHART',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Data kapabilitas N30 (CP,CPK)',
                'order' => 16,
            ],
            [
                'name' => 'N1 Data',
                'code' => 'N1_DATA',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Data N1',
                'order' => 17,
            ],
        ];

        foreach ($documents as $doc) {
            DocumentType::create($doc);
        }
    }
}
