<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Debugging Portal IS Detection...\n";
echo "=================================\n\n";

$doc = \App\Models\RequestDocument::find(1);

if (!$doc) {
    echo "Document ID 1 not found!\n";
    exit;
}

echo "Document ID: " . $doc->id . "\n";
echo "Status: " . $doc->status . "\n";
echo "Document Type ID: " . $doc->document_type_id . "\n\n";

$docType = $doc->documentType;

if ($docType) {
    echo "✅ Document Type loaded:\n";
    echo "  - ID: " . $docType->id . "\n";
    echo "  - Name: " . $docType->name . "\n";
    echo "  - Code: " . $docType->code . "\n";
    echo "  - Is Mandatory: " . ($docType->is_mandatory ? 'Yes' : 'No') . "\n\n";
    
    echo "Checking condition: \$doc->documentType->code === 'IS'\n";
    if ($docType->code === 'IS') {
        echo "✅ Condition TRUE - should show IS form button\n";
    } else {
        echo "❌ Condition FALSE - will show upload area\n";
        echo "   Actual code value: '" . $docType->code . "'\n";
        echo "   Code length: " . strlen($docType->code) . "\n";
        echo "   Code bytes: " . bin2hex($docType->code) . "\n";
    }
} else {
    echo "❌ Document Type NOT loaded (relationship issue)\n";
}

echo "\n=================================\n";
echo "Checking view template...\n\n";

$viewPath = resource_path('views/portal/upload.blade.php');
$content = file_get_contents($viewPath);

if (strpos($content, "@if(\$doc->documentType->code === 'IS')") !== false) {
    echo "✅ Conditional check EXISTS in upload.blade.php\n";
} else {
    echo "❌ Conditional check NOT FOUND in upload.blade.php\n";
}

if (strpos($content, "@include('portal.partials.inspection-standard-document')") !== false) {
    echo "✅ Include statement EXISTS in upload.blade.php\n";
} else {
    echo "❌ Include statement NOT FOUND in upload.blade.php\n";
}

echo "\n=================================\n";
echo "Checking partial file...\n\n";

$partialPath = resource_path('views/portal/partials/inspection-standard-document.blade.php');
if (file_exists($partialPath)) {
    echo "✅ Partial file EXISTS at:\n";
    echo "   " . $partialPath . "\n";
} else {
    echo "❌ Partial file NOT FOUND\n";
}
