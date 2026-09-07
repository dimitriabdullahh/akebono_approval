<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DocumentRequest;
use App\Models\Supplier;
use App\Models\User;
use App\Models\RequestPart;

$customToken = 'XlGrmurxKa1JrENEWqYCpkQQZ0xbFoxWI2bQQxAzuBJOtAi3ZhVLpRL2866T22p8';

echo "╔════════════════════════════════════════════════════════════════════════╗\n";
echo "║         CREATE DOCUMENT REQUEST WITH CUSTOM TOKEN                      ║\n";
echo "╚════════════════════════════════════════════════════════════════════════╝\n\n";

try {
    // Check if token already exists
    $existingRequest = DocumentRequest::where('access_token', $customToken)->first();
    if ($existingRequest) {
        echo "⚠️  Request dengan token ini sudah ada!\n\n";
        echo "Request ID: {$existingRequest->id}\n";
        echo "Supplier: {$existingRequest->supplier->name}\n\n";
        
        $appUrl = env('APP_URL', 'http://127.0.0.1:8000');
        $portalUrl = "{$appUrl}/supplier/portal/{$customToken}";
        
        echo "🌐 Portal Link:\n";
        echo str_repeat("═", 70) . "\n";
        echo "{$portalUrl}\n";
        echo str_repeat("═", 70) . "\n";
        exit(0);
    }
    
    // Get first supplier
    $supplier = Supplier::first();
    if (!$supplier) {
        echo "❌ ERROR: Tidak ada supplier di database!\n";
        echo "Jalankan: php artisan db:seed --class=SupplierSeeder\n";
        exit(1);
    }
    
    // Get or create admin user (any admin role)
    $admin = User::whereIn('role', ['QA', 'PURCHASING', 'PPC'])
        ->orWhere('role', 'LIKE', '%ADMIN%')
        ->first();
    
    if (!$admin) {
        // Just use any user if no specific admin found
        $admin = User::first();
    }
    
    if (!$admin) {
        echo "❌ ERROR: Tidak ada user di database!\n";
        echo "Jalankan: php artisan user:create-admins\n";
        exit(1);
    }
    
    // Create document request with custom token
    $request = DocumentRequest::create([
        'supplier_id' => $supplier->id,
        'model' => 'Test Model - Custom Token',
        'part_name' => 'Test Part',
        'part_number' => 'PART-001',
        'due_date' => now()->addDays(30)->format('Y-m-d'),
        'access_token' => $customToken,
        'created_by' => $admin->id,
    ]);
    
    // Create request parts
    RequestPart::create([
        'document_request_id' => $request->id,
        'part_name' => 'Test Part 1',
        'part_number' => 'PART-001',
    ]);
    
    echo "✅ Document Request berhasil dibuat dengan custom token!\n\n";
    echo str_repeat("─", 70) . "\n";
    echo "Request ID:       {$request->id}\n";
    echo "Supplier:         {$supplier->name}\n";
    echo "Vendor Code:      {$supplier->vendor_code}\n";
    echo "Model:            {$request->model}\n";
    echo "Due Date:         {$request->due_date}\n";
    echo str_repeat("─", 70) . "\n\n";
    
    $appUrl = env('APP_URL', 'http://127.0.0.1:8000');
    $portalUrl = "{$appUrl}/supplier/portal/{$customToken}";
    
    echo "🌐 SUPPLIER PORTAL ACCESS LINK:\n";
    echo str_repeat("═", 70) . "\n";
    echo "{$portalUrl}\n";
    echo str_repeat("═", 70) . "\n\n";
    
    echo "📧 PIC Contact:\n";
    echo "   Name:  {$supplier->pic_name}\n";
    echo "   Email: {$supplier->pic_email}\n";
    echo "   Phone: {$supplier->pic_phone}\n\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

echo str_repeat("═", 70) . "\n";
