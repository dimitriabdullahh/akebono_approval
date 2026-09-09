<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PackagingStandard;
use Barryvdh\DomPDF\Facade\Pdf;

// Find or mock a packaging standard
$ps = PackagingStandard::first();
if (!$ps) {
    $ps = new PackagingStandard();
    $ps->part_no = '47750-0K060';
    $ps->part_name = 'CYLINDER SUB-ASSY, DISC BRAKE, RR RH';
    $ps->customer = 'PT. TOYOTA MOTOR MANUFACTURING INDONESIA';
    $ps->document_no = 'PS-TMMIN-2026-001';
    $ps->revision = '00';
    $ps->date = now();
    $ps->packaging_type = 'PLASTIC BOX';
    $ps->packaging_size_mm = '400 x 300 x 148';
    $ps->packaging_lot_pcs_box = 4;
    $ps->net_weight_g_pc = 3250;
    $ps->total_weight_kg_box = 13.5;
}

$formData = [
    'generalInformation' => [
        'documentNo' => '',
        'revision' => '',
        'date' => '',
        'customer' => '',
        'partNo' => '',
        'partName' => '',
    ],
    'specifications' => [
        'packagingType' => '',
        'packagingSizeMm' => '',
        'packagingLotPcsBox' => '',
        'netWeightGPc' => '',
        'totalWeightKgBox' => '',
        'materials' => []
    ],
    'visualizationApproval' => [
        'approvedByName' => '',
        'approvedByDate' => '',
        'checkedByName' => '',
        'checkedByDate' => '',
        'preparedByName' => '',
        'preparedByDate' => '',
        'revisionHistory' => []
    ]
];

$pdf = Pdf::loadView('packaging-standard.pdf.template', [
    'ps' => $ps,
    'formData' => $formData
]);

$pdf->setPaper([0, 0, 792, 612], 'landscape');
$pdf->render();

$canvas = $pdf->getCanvas();
$pageCount = $canvas->get_page_count();
echo "Generated PDF Page Count: " . $pageCount . "\n";

file_put_contents(__DIR__ . '/output_ps.pdf', $pdf->output());
echo "Saved to output_ps.pdf\n";
