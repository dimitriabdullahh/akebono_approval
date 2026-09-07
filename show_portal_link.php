<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DocumentRequest;

$token = 'XlGrmurxKa1JrENEWqYCpkQQZ0xbFoxWI2bQQxAzuBJOtAi3ZhVLpRL2866T22p8';

echo "╔════════════════════════════════════════════════════════════════════════╗\n";
echo "║              SUPPLIER PORTAL ACCESS LINK                               ║\n";
echo "╚════════════════════════════════════════════════════════════════════════╝\n\n";

try {
    $request = DocumentRequest::where('access_token', $token)
        ->with(['supplier', 'requestParts', 'requestDocuments.documentType'])
        ->first();
    
    if (!$request) {
        echo "❌ ERROR: Token tidak ditemukan!\n";
        exit(1);
    }
    
    echo "✅ Document Request Ditemukan!\n\n";
    echo str_repeat("─", 70) . "\n";
    echo sprintf("%-20s: %d\n", "Request ID", $request->id);
    echo sprintf("%-20s: %s\n", "Supplier", $request->supplier->name);
    echo sprintf("%-20s: %s\n", "Vendor Code", $request->supplier->vendor_code);
    echo sprintf("%-20s: %s\n", "Model", $request->model);
    echo sprintf("%-20s: %s\n", "Due Date", $request->due_date);
    echo str_repeat("─", 70) . "\n\n";
    
    // Show parts
    if ($request->requestParts->isNotEmpty()) {
        echo "📦 Parts yang Diminta:\n";
        foreach ($request->requestParts as $index => $part) {
            echo sprintf("   %d. %s (Part No: %s)\n", $index + 1, $part->part_name, $part->part_number);
        }
        echo "\n";
    }
    
    // Show required documents
    $docCount = $request->requestDocuments->count();
    echo "📄 Total Dokumen yang Diminta: {$docCount} dokumen\n\n";
    
    // PIC Contact
    echo "📧 PIC Contact:\n";
    echo sprintf("   %-10s: %s\n", "Name", $request->supplier->pic_name);
    echo sprintf("   %-10s: %s\n", "Email", $request->supplier->pic_email);
    echo sprintf("   %-10s: %s\n", "Phone", $request->supplier->pic_phone);
    echo "\n";
    
    echo str_repeat("═", 70) . "\n";
    echo "🌐 SUPPLIER PORTAL ACCESS LINKS:\n";
    echo str_repeat("═", 70) . "\n\n";
    
    // Generate multiple URL options
    $urls = [
        "http://127.0.0.1:13000/supplier/portal/{$token}",
        "http://localhost:13000/supplier/portal/{$token}",
        "http://127.0.0.1:8000/supplier/portal/{$token}",
        "http://localhost:8000/supplier/portal/{$token}",
    ];
    
    echo "Pilih salah satu URL berikut (sesuaikan dengan port yang aktif):\n\n";
    foreach ($urls as $index => $url) {
        echo sprintf("%d. %s\n", $index + 1, $url);
    }
    
    echo "\n" . str_repeat("═", 70) . "\n\n";
    
    echo "💡 Tips:\n";
    echo "   • Copy link di atas dan buka di browser\n";
    echo "   • Link ini dapat dibagikan ke supplier via email\n";
    echo "   • Supplier dapat menggunakan link ini untuk upload dokumen\n";
    echo "   • Tidak perlu login, akses langsung dengan token\n\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo str_repeat("═", 70) . "\n";
