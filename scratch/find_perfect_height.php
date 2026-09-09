<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

for ($h = 45.0; $h <= 48.0; $h += 0.2) {
    $view = view('packaging-standard.pdf.template', [
        'ps' => App\Models\PackagingStandard::first() ?? new App\Models\PackagingStandard(),
        'formData' => [
            'generalInformation' => [],
            'specifications' => ['materials' => []],
            'visualizationApproval' => [
                'revisionHistory' => [],
                'preparedByName' => 'tes',
                'approvedByDate' => '26/09/26',
                'checkedByDate' => '26/09/26',
                'preparedByDate' => '26/09/26'
            ]
        ]
    ])->render();
    
    $modified = str_replace('height: 46pt;', 'height: ' . $h . 'pt;', $view);
    
    $pdf = Barryvdh\DomPDF\Facade\Pdf::loadHTML($modified);
    $pdf->setPaper([0, 0, 792, 612], 'landscape');
    file_put_contents(__DIR__ . '/test_h.pdf', $pdf->output());
    
    $json = shell_exec('python ' . escapeshellarg(__DIR__ . '/check_test_h.py'));
    echo "h = $h => $json\n";
}
