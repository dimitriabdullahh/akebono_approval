<?php

namespace App\Console\Commands;

use App\Models\DocumentRequest;
use App\Models\DocumentRequestReminder;
use App\Models\Supplier;
use App\Services\RequestService;
use Illuminate\Console\Command;

class TestAutoReminder extends Command
{
    protected $signature = 'test:auto-reminder';
    protected $description = 'Test otomatis pembuatan reminder saat request dibuat';

    public function handle()
    {
        $this->info('🧪 Testing auto-reminder creation...');

        // Ambil supplier pertama
        $supplier = Supplier::first();
        if (!$supplier) {
            $this->error('No supplier found!');
            return 1;
        }

        $this->info("Using supplier: {$supplier->name}");

        // Buat request baru menggunakan service
        $requestService = app(RequestService::class);
        
        $data = [
            'supplier_id' => $supplier->id,
            'model' => 'Test Model ' . now()->format('His'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'parts' => [
                [
                    'part_name' => 'Test Part 1',
                    'part_number' => 'TP-001',
                ],
            ],
            'selected_documents' => [1, 2, 3], // Select first 3 document types
        ];

        $request = $requestService->createRequestWithParts($data, 1);

        $this->info("✅ Request created: #{$request->id}");

        // Check apakah reminder otomatis terbuat
        $reminder = DocumentRequestReminder::where('document_request_id', $request->id)->first();

        if ($reminder) {
            $this->info("✅ Reminder auto-created!");
            $this->table(
                ['Field', 'Value'],
                [
                    ['ID', $reminder->id],
                    ['Request ID', $reminder->document_request_id],
                    ['Level', $reminder->reminder_level],
                    ['Status', $reminder->escalation_status],
                    ['Next Reminder At', $reminder->next_reminder_at],
                ]
            );
            
            $minutesUntilReminder = now()->diffInMinutes($reminder->next_reminder_at, false);
            $this->info("⏰ Next reminder in: {$minutesUntilReminder} minutes");
            
            return 0;
        } else {
            $this->error("❌ Reminder NOT auto-created!");
            return 1;
        }
    }
}
