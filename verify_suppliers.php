<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Supplier;
use App\Models\DocumentRequest;
use App\Models\RequestDocument;

echo "\n" . str_repeat("═", 70) . "\n";
echo "SUPPLIER CREATION VERIFICATION\n";
echo str_repeat("═", 70) . "\n\n";

// Get latest 5 suppliers
$suppliers = Supplier::latest()->limit(5)->get();
echo "📋 Latest 5 Suppliers:\n";
echo str_repeat("─", 70) . "\n";
foreach ($suppliers as $s) {
    echo sprintf("%2d. %-35s | %12s | %s\n", $s->id, substr($s->name, 0, 33), $s->vendor_code, $s->pic_email);
}

// Statistics
$totalSuppliers = Supplier::count();
$totalRequests = DocumentRequest::count();
$totalDocuments = RequestDocument::count();

echo "\n📊 Statistics:\n";
echo str_repeat("─", 70) . "\n";
echo sprintf("Total Suppliers:           %3d\n", $totalSuppliers);
echo sprintf("Total Document Requests:   %3d\n", $totalRequests);
echo sprintf("Total Request Documents:   %3d\n", $totalDocuments);

// Calculate document types
$docTypes = \App\Models\DocumentType::count();
echo sprintf("Document Types Available:  %3d\n", $docTypes);

echo "\n✅ Verification Complete!\n";
echo str_repeat("═", 70) . "\n\n";
