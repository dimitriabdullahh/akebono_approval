<?php

namespace App\Console\Commands;

use App\Models\DocumentType;
use Illuminate\Console\Command;

class ShowDocumentTypes extends Command
{
    protected $signature = 'document-types:show';
    protected $description = 'Show all document types in the system';

    public function handle()
    {
        $this->info('=== DOCUMENT TYPES IN SYSTEM ===');

        $mandatory = DocumentType::where('is_mandatory', true)->orderBy('name')->get();
        $optional = DocumentType::where('is_mandatory', false)->orderBy('name')->get();

        $this->newLine();
        $this->info('📋 MANDATORY DOCUMENTS (' . $mandatory->count() . ' items):');
        $this->newLine();

        foreach ($mandatory as $index => $doc) {
            $scopes = is_array($doc->scope) ? implode(', ', $doc->scope) : $doc->scope;
            $this->line(($index + 1) . '. ' . $doc->name . ' (' . $doc->code . ') - Scope: [' . $scopes . ']');
        }

        $this->newLine();
        $this->info('📄 OPTIONAL DOCUMENTS (' . $optional->count() . ' items):');
        $this->newLine();

        foreach ($optional as $index => $doc) {
            $scopes = is_array($doc->scope) ? implode(', ', $doc->scope) : $doc->scope;
            $this->line(($index + 1) . '. ' . $doc->name . ' (' . $doc->code . ') - Scope: [' . $scopes . ']');
        }

        $this->newLine();
        $this->info('Total: ' . DocumentType::count() . ' document types');

        return Command::SUCCESS;
    }
}