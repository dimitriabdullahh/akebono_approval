<?php

namespace App\Console\Commands;

use App\Models\MsaGageRr;
use Illuminate\Console\Command;

class ShowMsaRecords extends Command
{
    protected $signature = 'msa:show';
    protected $description = 'Show all MSA Gage R&R records';

    public function handle()
    {
        $this->info('📊 MSA Gage R&R Records:');
        $this->newLine();

        $records = MsaGageRr::with(['requestDocument.requestPart', 'supplier'])->get();

        if ($records->isEmpty()) {
            $this->warn('No MSA records found!');
            $this->info('💡 Create a document request with MSA document type to generate MSA form.');
            return 1;
        }

        $tableData = [];
        foreach ($records as $msa) {
            $partNo = 'N/A';
            $partName = 'N/A';
            
            if ($msa->requestDocument && $msa->requestDocument->requestPart) {
                $partNo = $msa->requestDocument->requestPart->part_number ?? 'N/A';
                $partName = $msa->requestDocument->requestPart->part_name ?? 'N/A';
            }
            
            $tableData[] = [
                $msa->id,
                $msa->token,
                $partNo,
                $partName,
                $msa->status ?? 'DRAFT',
                $msa->supplier->name ?? 'N/A',
            ];
        }

        $this->table(
            ['ID', 'Token', 'Part No', 'Part Name', 'Status', 'Supplier'],
            $tableData
        );

        $this->newLine();
        $this->info('✅ Total: ' . $records->count() . ' MSA records');
        $this->info('🌐 Access URL: http://127.0.0.1:8000/supplier/msa/{token}');

        return 0;
    }
}
