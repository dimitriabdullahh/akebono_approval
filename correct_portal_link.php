<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DocumentRequest;

$token = 'XlGrmurxKa1JrENEWqYCpkQQZ0xbFoxWI2bQQxAzuBJOtAi3ZhVLpRL2866T22p8';

echo "╔════════════════════════════════════════════════════════════════════════╗\n";
echo "║              CORRECT SUPPLIER PORTAL ACCESS LINK                       ║\n";
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
    
    echo str_repeat("═", 70) . "\n";
    echo "🌐 CORRECT SUPPLIER PORTAL LINKS:\n";
    echo str_repeat("═", 70) . "\n\n";
    
    echo "Ada 2 versi route untuk supplier portal:\n\n";
    
    echo "1️⃣  NEW SUPPLIER PORTAL (Optimized UI):\n";
    echo "   http://127.0.0.1:13000/supplier/{$token}\n\n";
    
    echo "2️⃣  OLD PORTAL (Legacy):\n";
    echo "   http://127.0.0.1:13000/portal/{$token}\n\n";
    
    echo str_repeat("═", 70) . "\n\n";
    
    echo "📌 RECOMMENDED: Gunakan link #1 (NEW SUPPLIER PORTAL)\n\n";
    
    echo "📋 Supplier Info:\n";
    echo "   Name:  {$request->supplier->name}\n";
    echo "   PIC:   {$request->supplier->pic_name}\n";
    echo "   Email: {$request->supplier->pic_email}\n";
    echo "   Phone: {$request->supplier->pic_phone}\n\n";
    
    // Show parts
    if ($request->requestParts->isNotEmpty()) {
        echo "📦 Parts:\n";
        foreach ($request->requestParts as $part) {
            echo "   • {$part->part_name} ({$part->part_number})\n";
        }
        echo "\n";
    }
    
    // Show documents
    $docCount = $request->requestDocuments->count();
    echo "📄 Documents: {$docCount} dokumen yang diminta\n\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo str_repeat("═", 70) . "\n";
