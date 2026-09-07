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
            [
                'name' => 'Process Flow Diagram (PFD)',
                'code' => 'PFD',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Diagram alur proses produksi',
            ],
            [
                'name' => 'Process Failure Mode dan Effect Analysis (FMEA)',
                'code' => 'FMEA',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Analisis mode kegagalan proses dan dampaknya',
            ],
            [
                'name' => 'Quality Control Process Chart (QCPC)',
                'code' => 'QCPC',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Chart proses kontrol kualitas',
            ],
            [
                'name' => 'Measurement System Analysis (MSA)',
                'code' => 'MSA',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Analisis sistem pengukuran',
            ],
            [
                'name' => 'Inspection Standard (IS)',
                'code' => 'IS',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Standar inspeksi',
            ],
            [
                'name' => 'Initial Sample Inspection Report (ISIR)',
                'code' => 'ISIR',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Laporan inspeksi sampel awal',
            ],
            [
                'name' => 'X-R Control Chart (CP-CK)',
                'code' => 'XR_CONTROL_CHART',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Chart kontrol X-R (CP-CK)',
            ],
            [
                'name' => 'Appearance Approval Report (AAR)',
                'code' => 'AAR',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Laporan persetujuan tampilan',
            ],
            [
                'name' => 'Limit Sample Tag',
                'code' => 'LIMIT_SAMPLE_TAG',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Tag sampel batas',
            ],
            [
                'name' => 'Packaging Standard (PS)',
                'code' => 'PACKAGING_STANDARD',
                'is_mandatory' => true,
                'scope' => ['PURCHASING', 'PPC', 'QA'],
                'description' => 'Standar kemasan',
            ],
            [
                'name' => 'Part Submission Warrant (PSW)',
                'code' => 'PSW',
                'is_mandatory' => true,
                'scope' => ['PURCHASING', 'QA'],
                'description' => 'Surat jaminan pengajuan part',
            ],
            [
                'name' => 'Mill Sheet Material (Dimensi, Material, Colour-check, dll.)',
                'code' => 'MILL_SHEET_MATERIAL',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Lembar material mill dengan dimensi, material, colour-check, dll.',
            ],
            [
                'name' => 'Certificate of Calibration',
                'code' => 'CERTIFICATE_CALIBRATION',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Sertifikat kalibrasi',
            ],
            [
                'name' => 'Drawing Product',
                'code' => 'DRAWING_PRODUCT',
                'is_mandatory' => true,
                'scope' => ['QA'],
                'description' => 'Gambar produk',
            ],
            [
                'name' => 'SOC Free',
                'code' => 'SOC_FREE',
                'is_mandatory' => false,
                'scope' => ['QA'],
                'description' => 'Sertifikat SOC Free (Optional)',
            ],
            [
                'name' => 'IMDS (International Material Data System)',
                'code' => 'IMDS',
                'is_mandatory' => false,
                'scope' => ['QA'],
                'description' => 'International Material Data System (Optional)',
            ],
        ];

        foreach ($documents as $doc) {
            DocumentType::create($doc);
        }
    }
}
