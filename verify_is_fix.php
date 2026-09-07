<?php

require __DIR__.'/vendor/autoload.php';

$viewPath = __DIR__ . '/resources/views/portal/upload.blade.php';
$content = file_get_contents($viewPath);

echo "Verifying IS Check Fix in upload.blade.php...\n";
echo "=============================================\n\n";

// Count IS checks
$isCheckCount = substr_count($content, "@if(\$doc->documentType->code === 'IS')");
echo "✓ IS conditional checks found: " . $isCheckCount . "\n";

if ($isCheckCount >= 2) {
    echo "  ✅ GOOD - Both multi-part and non-multi-part sections covered\n";
} else if ($isCheckCount == 1) {
    echo "  ⚠️  WARNING - Only one section has IS check\n";
} else {
    echo "  ❌ ERROR - No IS checks found!\n";
}

// Count include statements
$includeCount = substr_count($content, "@include('portal.partials.inspection-standard-document'");
echo "\n✓ Include statements found: " . $includeCount . "\n";

if ($includeCount == $isCheckCount) {
    echo "  ✅ GOOD - Each IS check has corresponding include\n";
} else {
    echo "  ❌ ERROR - Mismatch between checks and includes\n";
}

// Verify partial file exists
$partialPath = __DIR__ . '/resources/views/portal/partials/inspection-standard-document.blade.php';
if (file_exists($partialPath)) {
    echo "\n✅ Partial file EXISTS:\n";
    echo "   " . $partialPath . "\n";
} else {
    echo "\n❌ Partial file NOT FOUND\n";
}

// Check for balanced if/endif
$ifCount = substr_count($content, '@if(');
$endifCount = substr_count($content, '@endif');
$elseCount = substr_count($content, '@else');

echo "\n\nBlade Directive Balance:\n";
echo "  @if statements: " . $ifCount . "\n";
echo "  @else statements: " . $elseCount . "\n";
echo "  @endif statements: " . $endifCount . "\n";

if ($ifCount == $endifCount) {
    echo "  ✅ Balanced - Every @if has @endif\n";
} else {
    echo "  ⚠️  WARNING - Unbalanced if/endif (may be false positive due to nested structures)\n";
}

echo "\n=============================================\n";
echo "SUMMARY:\n";
if ($isCheckCount >= 2 && $includeCount == $isCheckCount && file_exists($partialPath)) {
    echo "✅ ALL CHECKS PASSED - IS documents should now display correctly!\n";
    echo "\nNext steps:\n";
    echo "1. Refresh your browser (Ctrl+F5)\n";
    echo "2. Clear Laravel view cache: php artisan view:clear\n";
    echo "3. Check portal again\n";
} else {
    echo "❌ SOME CHECKS FAILED - Review the output above\n";
}

echo "\n";
