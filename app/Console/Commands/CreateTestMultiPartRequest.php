<?php

namespace App\Console\Commands;

use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\RequestDocument;
use App\Models\RequestPart;
use App\Models\Supplier;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateTestMultiPartRequest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:create-multipart-request 
                            {--supplier= : Supplier ID (optional, will use first supplier if not specified)}
                            {--parts=3 : Number of parts to create (default: 3)}
                            {--docs=5 : Number of documents per part (default: 5, use "all" for all 16)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a test request with multiple parts for testing multi-part functionality';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🚀 Creating test multi-part request...');
        $this->newLine();

        // Get or select supplier
        $supplierId = $this->option('supplier');
        if (!$supplierId) {
            $supplier = Supplier::first();
            if (!$supplier) {
                $this->error('❌ No suppliers found in database. Please create a supplier first.');
                return 1;
            }
            $supplierId = $supplier->id;
        } else {
            $supplier = Supplier::find($supplierId);
            if (!$supplier) {
                $this->error("❌ Supplier with ID {$supplierId} not found.");
                return 1;
            }
        }

        $this->info("✓ Supplier: {$supplier->name} ({$supplier->vendor_code})");

        // Get number of parts
        $numParts = (int) $this->option('parts');
        if ($numParts < 1 || $numParts > 10) {
            $this->error('❌ Number of parts must be between 1 and 10.');
            return 1;
        }

        // Get documents
        $docsOption = $this->option('docs');
        if ($docsOption === 'all') {
            $documentTypes = DocumentType::all();
        } else {
            $numDocs = (int) $docsOption;
            if ($numDocs < 1) {
                $this->error('❌ Number of documents must be at least 1.');
                return 1;
            }
            $documentTypes = DocumentType::take($numDocs)->get();
        }

        if ($documentTypes->isEmpty()) {
            $this->error('❌ No document types found in database.');
            return 1;
        }

        $this->info("✓ Documents per part: {$documentTypes->count()}");
        $this->newLine();

        // Create document request
        $accessToken = Str::random(64);
        
        $request = DocumentRequest::create([
            'supplier_id' => $supplier->id,
            'model' => 'Model A - Test Multi-Part',
            'due_date' => now()->addDays(30),
            'access_token' => $accessToken,
            'created_by' => 1, // Assuming admin user ID 1
        ]);

        $this->info("✓ Created Request ID: {$request->id}");
        $this->info("✓ Model: {$request->model}");
        $this->info("✓ Due Date: {$request->due_date->format('d M Y')}");
        $this->newLine();

        // Create parts
        $partNames = [
            'Brake Pad Front',
            'Brake Pad Rear',
            'Brake Disc Front',
            'Brake Disc Rear',
            'Brake Caliper Front',
            'Brake Caliper Rear',
            'Master Cylinder',
            'Brake Hose',
            'Wheel Cylinder',
            'Parking Brake Cable',
        ];

        $this->info("📦 Creating {$numParts} parts:");
        $this->newLine();

        for ($i = 0; $i < $numParts; $i++) {
            $partName = $partNames[$i] ?? "Part " . ($i + 1);
            $partNumber = 'AK-' . str_pad(148510 + $i, 6, '0', STR_PAD_LEFT) . '-RR';

            $part = RequestPart::create([
                'document_request_id' => $request->id,
                'part_name' => $partName,
                'part_number' => $partNumber,
                'submission_status' => 'DRAFT',
            ]);

            $this->line("  Part " . ($i + 1) . ": {$partName} ({$partNumber})");

            // Create documents for this part
            $docCount = 0;
            foreach ($documentTypes as $docType) {
                RequestDocument::create([
                    'document_request_id' => $request->id,
                    'request_part_id' => $part->id,
                    'document_type_id' => $docType->id,
                    'status' => 'PENDING',
                ]);
                $docCount++;
            }

            $this->line("    → Created {$docCount} documents");
        }

        $this->newLine();
        $this->info('✅ Test request created successfully!');
        $this->newLine();

        // Display access information
        $this->line('═══════════════════════════════════════════════════');
        $this->info('📋 REQUEST DETAILS:');
        $this->line('═══════════════════════════════════════════════════');
        $this->line("Request ID       : {$request->id}");
        $this->line("Supplier         : {$supplier->name}");
        $this->line("Model            : {$request->model}");
        $this->line("Due Date         : {$request->due_date->format('d M Y')}");
        $this->line("Total Parts      : {$numParts}");
        $this->line("Docs per Part    : {$documentTypes->count()}");
        $this->line("Total Documents  : " . ($numParts * $documentTypes->count()));
        $this->newLine();

        $this->line('═══════════════════════════════════════════════════');
        $this->info('🔗 ACCESS LINKS:');
        $this->line('═══════════════════════════════════════════════════');
        
        $portalUrl = url("/supplier/upload/{$accessToken}");
        $this->line("Portal URL:");
        $this->comment("  {$portalUrl}");
        $this->newLine();

        $this->line("Access Token:");
        $this->comment("  {$accessToken}");
        $this->newLine();

        // Display parts breakdown
        $this->line('═══════════════════════════════════════════════════');
        $this->info('📦 PARTS BREAKDOWN:');
        $this->line('═══════════════════════════════════════════════════');
        
        $parts = $request->requestParts()->with('requestDocuments')->get();
        foreach ($parts as $idx => $part) {
            $this->line("Part " . ($idx + 1) . ": {$part->part_name}");
            $this->line("  Number: {$part->part_number}");
            $this->line("  Status: {$part->submission_status}");
            $this->line("  Documents: {$part->requestDocuments->count()}");
            $this->newLine();
        }

        $this->line('═══════════════════════════════════════════════════');
        $this->info('🧪 TESTING CHECKLIST:');
        $this->line('═══════════════════════════════════════════════════');
        $this->line('[ ] 1. Open portal URL and verify all parts are displayed vertically');
        $this->line('[ ] 2. Verify each part shows correct name, number, and progress');
        $this->line('[ ] 3. Upload documents to Part 1 and click "Upload Documents"');
        $this->line('[ ] 4. Verify progress updates for Part 1 only');
        $this->line('[ ] 5. Upload documents to Part 2 and Part 3');
        $this->line('[ ] 6. Click "Save Draft" and verify success message');
        $this->line('[ ] 7. Refresh page and verify all uploads are saved');
        $this->line('[ ] 8. Try "Submit All" with incomplete docs (should fail with details)');
        $this->line('[ ] 9. Complete all mandatory docs for all parts');
        $this->line('[ ] 10. Click "Submit All" and verify success');
        $this->line('[ ] 11. Verify all parts status changed to SUBMITTED');
        $this->line('[ ] 12. Verify total progress shows 100%');
        $this->newLine();

        return 0;
    }
}
