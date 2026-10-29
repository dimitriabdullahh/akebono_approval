<?php

namespace App\Console\Commands;

use App\Models\DocumentType;
use Illuminate\Console\Command;

class ShowDocumentOrder extends Command
{
    protected $signature = 'documents:show-order';
    protected $description = 'Show document types in order';

    public function handle()
    {
        $this->info('📋 Document Types Order:');
        $this->newLine();

        $docs = DocumentType::orderBy('order')->get();

        $tableData = [];
        foreach ($docs as $doc) {
            $tableData[] = [
                $doc->order,
                $doc->name,
                $doc->code,
                $doc->is_mandatory ? '✓ Mandatory' : '○ Optional',
            ];
        }

        $this->table(
            ['Order', 'Name', 'Code', 'Status'],
            $tableData
        );

        $this->newLine();
        $this->info('✅ Total: ' . $docs->count() . ' document types');

        return 0;
    }
}
