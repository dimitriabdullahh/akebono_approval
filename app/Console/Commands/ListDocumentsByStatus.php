<?php

namespace App\Console\Commands;

use App\Models\RequestDocument;
use Illuminate\Console\Command;

class ListDocumentsByStatus extends Command
{
    protected $signature = 'documents:list-by-status 
                            {--status= : Filter by status (NOT_UPLOADED|SUBMITTED|REVISE|APPROVED)}';

    protected $description = 'List all request documents grouped by status';

    public function handle()
    {
        $status = strtoupper($this->option('status'));

        $query = RequestDocument::with([
            'documentRequest.supplier',
            'documentType',
            'reviewedBy'
        ])->orderBy('updated_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        $documents = $query->get();

        if ($documents->isEmpty()) {
            $this->warn('No documents found.');
            return 0;
        }

        // Group by status
        $grouped = $documents->groupBy('status');

        foreach ($grouped as $statusName => $docs) {
            $icon = match($statusName) {
                'NOT_UPLOADED' => '⚪',
                'SUBMITTED' => '🟡',
                'REVISE' => '🟠',
                'APPROVED' => '🟢',
                default => '⚫'
            };

            $this->newLine();
            $this->info("=== {$icon} {$statusName} ({$docs->count()}) ===");

            $data = $docs->map(function ($doc) {
                $reviewer = $doc->reviewedBy ? $doc->reviewedBy->name : '-';
                
                return [
                    'ID' => $doc->id,
                    'Supplier' => $doc->documentRequest->supplier->name ?? 'Unknown',
                    'Document' => $doc->documentType->name ?? 'Unknown',
                    'Model' => $doc->documentRequest->model ?? '-',
                    'Reviewer' => $reviewer,
                    'Updated' => $doc->updated_at->format('Y-m-d H:i'),
                ];
            })->values();

            $this->table(
                ['ID', 'Supplier', 'Document', 'Model', 'Reviewer', 'Updated'],
                $data
            );
        }

        // Statistics
        $this->newLine();
        $this->info('=== Document Statistics ===');
        foreach (['NOT_UPLOADED', 'SUBMITTED', 'REVISE', 'APPROVED'] as $stat) {
            $count = RequestDocument::where('status', $stat)->count();
            $icon = match($stat) {
                'NOT_UPLOADED' => '⚪',
                'SUBMITTED' => '🟡',
                'REVISE' => '🟠',
                'APPROVED' => '🟢',
                default => '⚫'
            };
            $this->line("  {$icon} {$stat}: {$count}");
        }

        $this->newLine();
        $this->line("Total documents: " . RequestDocument::count());

        return 0;
    }
}
