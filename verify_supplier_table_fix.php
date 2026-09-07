<?php

$viewPath = __DIR__ . '/resources/views/supplier/partials/documents-table.blade.php';
$content = file_get_contents($viewPath);

echo "Verifying IS Check Fix in supplier documents-table.blade.php...\n";
echo "===============================================================\n\n";

// Count IS checks
$isCheckCount = substr_count($content, "@if(\$doc->documentType->code === 'IS')");
echo "✓ IS conditional checks found: " . $isCheckCount . "\n";

if ($isCheckCount >= 1) {
    echo "  ✅ GOOD - IS check added to supplier table\n";
} else {
    echo "  ❌ ERROR - No IS checks found!\n";
}

// Check for IS-specific button text
$hasFormButton = strpos($content, 'Isi Form Terlebih Dahulu') !== false;
$hasContinueButton = strpos($content, 'Continue Form') !== false;

echo "\n✓ IS-specific buttons:\n";
echo "  Form button ('Isi Form Terlebih Dahulu'): " . ($hasFormButton ? '✅ Found' : '❌ Not found') . "\n";
echo "  Continue button ('Continue Form'): " . ($hasContinueButton ? '✅ Found' : '❌ Not found') . "\n";

// Check for PDF routes
$hasPdfRoute = strpos($content, "route('inspection-standard.pdf'") !== false;
$hasDownloadRoute = strpos($content, "route('inspection-standard.download'") !== false;

echo "\n✓ IS routes:\n";
echo "  PDF view route: " . ($hasPdfRoute ? '✅ Found' : '❌ Not found') . "\n";
echo "  PDF download route: " . ($hasDownloadRoute ? '✅ Found' : '❌ Not found') . "\n";

// Check for balanced if/endif
$ifCount = substr_count($content, '@if(');
$endifCount = substr_count($content, '@endif');

echo "\n\nBlade Directive Balance:\n";
echo "  @if statements: " . $ifCount . "\n";
echo "  @endif statements: " . $endifCount . "\n";

if ($ifCount == $endifCount) {
    echo "  ✅ Balanced\n";
} else {
    echo "  ⚠️  WARNING - Unbalanced (difference: " . abs($ifCount - $endifCount) . ")\n";
}

echo "\n===============================================================\n";
echo "SUMMARY:\n";
if ($isCheckCount >= 1 && $hasFormButton && $hasPdfRoute && $ifCount == $endifCount) {
    echo "✅ ALL CHECKS PASSED - IS documents should now display with form button!\n";
    echo "\nNext steps:\n";
    echo "1. View cache cleared\n";
    echo "2. Refresh your browser (Ctrl+F5)\n";
    echo "3. Check supplier portal again\n";
    echo "4. You should see 'Isi Form Terlebih Dahulu' button instead of 'Upload Document'\n";
} else {
    echo "❌ SOME CHECKS FAILED - Review the output above\n";
}

echo "\n";
