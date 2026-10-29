<?php

namespace App\Console\Commands;

use App\Models\DocumentType;
use Illuminate\Console\Command;

class ConfigureDocumentTypeScopes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:configure-scopes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configure document type scopes for multi-division approval workflow';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Configuring document type scopes...');

        $scopes = [
            'IS' => ['QA'], // Inspection Standard: QA only
            'PACKAGING_STANDARD' => ['QA', 'PURCHASING', 'PPC'], // Packaging Standard: Multi-approval
            'PSW' => ['QA', 'PURCHASING'], // Part Submission Warrant: QA + Purchasing
        ];

        foreach ($scopes as $code => $scopeArray) {
            $documentType = DocumentType::where('code', $code)->first();

            if (!$documentType) {
                $this->warn("⚠ Document type '{$code}' not found, skipping...");
                continue;
            }

            $documentType->scope = $scopeArray;
            $documentType->save();

            $scopeString = implode(', ', $scopeArray);
            $this->info("✓ {$documentType->name} ({$code}): [{$scopeString}]");
        }

        $this->newLine();
        $this->info('Document type scopes configured successfully!');
        $this->newLine();
        $this->comment('Workflow summary:');
        $this->comment('  IS (Inspection Standard):');
        $this->comment('    → Receiver (QA) → Checker (QA) → Approver (QA)');
        $this->newLine();
        $this->comment('  PSW (Part Submission Warrant):');
        $this->comment('    → Checker (QA) → Approver (QA) → Approver (Purchasing)');
        $this->newLine();
        $this->comment('  PS (Packaging Standard):');
        $this->comment('    → Checker (QA) → Approver (QA) → Approver (Purchasing) → Approver (PPC)');

        return Command::SUCCESS;
    }
}
