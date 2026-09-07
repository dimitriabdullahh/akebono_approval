<?php

namespace App\Console\Commands;

use App\Models\Supplier;
use App\Models\DocumentRequest;
use App\Services\OutstandingDocumentService;
use Illuminate\Console\Command;

class TestOutstandingAlert extends Command
{
    protected $signature = 'test:outstanding-alert {supplier_id?}';
    protected $description = 'Test outstanding alert functionality';

    public function handle()
    {
        $supplierId = $this->argument('supplier_id');

        if ($supplierId) {
            $this->testSpecificSupplier($supplierId);
        } else {
            $this->testAllSuppliers();
        }
    }

    private function testAllSuppliers()
    {
        $this->info('Testing Outstanding Alert for All Suppliers');
        $this->info('==============================================');
        $this->newLine();

        $suppliers = Supplier::with(['documentRequests'])->get();

        if ($suppliers->isEmpty()) {
            $this->error('No suppliers found in database!');
            return;
        }

        $this->table(
            ['ID', 'Name', 'Vendor Code', 'Total Requests', 'Outstanding'],
            $suppliers->map(function ($supplier) {
                $outstanding = $supplier->documentRequests->filter(function ($request) {
                    return $request->load('requestDocuments.documentType')->isOutstanding();
                })->count();

                return [
                    $supplier->id,
                    $supplier->name,
                    $supplier->vendor_code,
                    $supplier->documentRequests->count(),
                    $outstanding
                ];
            })
        );

        $this->newLine();
        $this->info('To test specific supplier: php artisan test:outstanding-alert {supplier_id}');
    }

    private function testSpecificSupplier($supplierId)
    {
        $supplier = Supplier::find($supplierId);

        if (!$supplier) {
            $this->error("Supplier with ID {$supplierId} not found!");
            return;
        }

        $this->info("Testing Outstanding Alert for Supplier: {$supplier->name}");
        $this->info('=======================================================');
        $this->newLine();

        // Test Service
        $service = app(OutstandingDocumentService::class);
        
        try {
            $outstanding = $service->getOutstandingForSupplier($supplierId);
            $statistics = $service->getStatistics($supplierId);

            $this->info('✓ Service works correctly');
            $this->newLine();

            // Display Statistics
            $this->info('Statistics:');
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total Outstanding', $statistics['total']],
                    ['Open', $statistics['open']],
                    ['Reject', $statistics['reject']],
                    ['Overdue', $statistics['overdue']],
                    ['On Track', $statistics['on_track']],
                ]
            );
            $this->newLine();

            // Display Outstanding Documents
            if ($outstanding->isEmpty()) {
                $this->warn('No outstanding documents found for this supplier.');
            } else {
                $this->info("Outstanding Documents ({$outstanding->count()}):");
                $this->table(
                    ['ID', 'Request Date', 'Model', 'Due Date', 'Status', 'Overdue?'],
                    $outstanding->map(function ($item) {
                        return [
                            $item['id'],
                            $item['request_date_formatted'],
                            $item['model'],
                            $item['due_date_formatted'],
                            $item['status_label'],
                            $item['is_overdue'] ? '⚠ YES' : 'No'
                        ];
                    })
                );
            }

            $this->newLine();

            // Test each request individually
            $requests = DocumentRequest::where('supplier_id', $supplierId)
                ->with(['requestDocuments.documentType'])
                ->get();

            $this->info("All Requests for Supplier ({$requests->count()}):");
            $this->table(
                ['ID', 'Model', 'Due Date', 'Project Category', 'Outstanding Status', 'Is Outstanding?'],
                $requests->map(function ($request) {
                    return [
                        $request->id,
                        $request->model,
                        $request->due_date->format('Y-m-d'),
                        $request->project_category,
                        $request->outstanding_status ?? 'null',
                        $request->isOutstanding() ? '✓ Yes' : 'No'
                    ];
                })
            );

            $this->newLine();
            $this->info('✓ All tests completed successfully!');

        } catch (\Exception $e) {
            $this->error('✗ Error occurred: ' . $e->getMessage());
            $this->error('Stack trace:');
            $this->line($e->getTraceAsString());
        }
    }
}
