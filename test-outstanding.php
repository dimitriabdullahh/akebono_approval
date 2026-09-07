<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Outstanding Alert ===\n\n";

// Test 1: Check total requests
$totalRequests = \App\Models\DocumentRequest::count();
echo "Total Document Requests: {$totalRequests}\n\n";

if ($totalRequests > 0) {
    // Test 2: Get first request
    $request = \App\Models\DocumentRequest::with('requestDocuments.documentType', 'supplier')->first();
    
    echo "Request Details:\n";
    echo "- ID: {$request->id}\n";
    echo "- Supplier: {$request->supplier->name}\n";
    echo "- Model: {$request->model}\n";
    echo "- Total Documents: " . $request->requestDocuments->count() . "\n";
    
    // Test 3: Check status
    echo "\nStatus Checks:\n";
    echo "- Project Category: {$request->project_category}\n";
    echo "- Outstanding Status: " . ($request->outstanding_status ?? 'null') . "\n";
    echo "- Is Outstanding: " . ($request->isOutstanding() ? 'YES' : 'NO') . "\n";
    echo "- Alert Dismissed At: " . ($request->alert_dismissed_at ? $request->alert_dismissed_at->format('Y-m-d H:i:s') : 'null') . "\n";
    
    // Test 4: Check portal URL
    try {
        echo "- Portal Upload URL: {$request->portal_upload_url}\n";
    } catch (\Exception $e) {
        echo "- Portal Upload URL Error: {$e->getMessage()}\n";
    }
    
    // Test 5: Use service
    echo "\n=== Testing Service ===\n";
    try {
        $service = app(\App\Services\OutstandingDocumentService::class);
        $outstanding = $service->getOutstandingForSupplier($request->supplier_id);
        echo "Outstanding Documents Count: " . $outstanding->count() . "\n";
        
        if ($outstanding->count() > 0) {
            echo "\nFirst Outstanding Document:\n";
            $first = $outstanding->first();
            echo "- ID: {$first['id']}\n";
            echo "- Model: {$first['model']}\n";
            echo "- Status: {$first['status']}\n";
            echo "- Due Date: {$first['due_date_formatted']}\n";
            echo "- Portal URL: {$first['portal_url']}\n";
        }
        
        // Test statistics
        $stats = $service->getStatistics($request->supplier_id);
        echo "\nStatistics:\n";
        echo "- Total: {$stats['total']}\n";
        echo "- Open: {$stats['open']}\n";
        echo "- Reject: {$stats['reject']}\n";
        echo "- Overdue: {$stats['overdue']}\n";
        
    } catch (\Exception $e) {
        echo "Service Error: {$e->getMessage()}\n";
        echo "Trace: {$e->getTraceAsString()}\n";
    }
    
} else {
    echo "No document requests found in database.\n";
}

echo "\n=== Test Complete ===\n";
