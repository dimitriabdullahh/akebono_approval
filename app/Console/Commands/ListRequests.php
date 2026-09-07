<?php

namespace App\Console\Commands;

use App\Models\DocumentRequest;
use Illuminate\Console\Command;

class ListRequests extends Command
{
    protected $signature = 'requests:list 
                            {--limit=20 : Maximum number of requests to show}
                            {--category= : Filter by category (not_submitted|in_progress|completed)}';

    protected $description = 'List all document requests with their details';

    public function handle()
    {
        $limit = $this->option('limit');
        $category = $this->option('category');

        $query = DocumentRequest::with(['supplier', 'requestParts', 'requestDocuments.documentType'])
            ->orderBy('id', 'desc');

        $requests = $query->limit($limit)->get();

        if ($category) {
            $requests = $requests->filter(function ($request) use ($category) {
                return $request->project_category === $category;
            });
        }

        if ($requests->isEmpty()) {
            $this->warn('No requests found.');
            return 0;
        }

        $this->info("Found {$requests->count()} document request(s):\n");

        $data = $requests->map(function ($request) {
            $categoryIcon = match($request->project_category) {
                'not_submitted' => '🔴',
                'in_progress' => '🟡',
                'completed' => '🟢',
                default => '⚪'
            };

            $categoryName = match($request->project_category) {
                'not_submitted' => 'Not Submitted',
                'in_progress' => 'In Progress',
                'completed' => 'Completed',
                default => 'Unknown'
            };

            return [
                'ID' => $request->id,
                'Supplier' => $request->supplier->name ?? 'Unknown',
                'Model' => $request->model ?? '-',
                'Status' => $categoryIcon . ' ' . $categoryName,
                'Progress' => $request->approval_progress . '%',
                'Parts' => $request->requestParts->count(),
                'Created' => $request->created_at->format('Y-m-d H:i'),
            ];
        })->values();

        $this->table(
            ['ID', 'Supplier', 'Model', 'Status', 'Progress', 'Parts', 'Created'],
            $data
        );

        // Statistics
        $this->newLine();
        $this->info('=== Request Statistics ===');
        $allRequests = DocumentRequest::with(['requestParts', 'requestDocuments.documentType'])->get();
        
        $categories = $allRequests->groupBy('project_category');
        
        foreach (['not_submitted', 'in_progress', 'completed'] as $cat) {
            $count = $categories->get($cat)?->count() ?? 0;
            $icon = match($cat) {
                'not_submitted' => '🔴',
                'in_progress' => '🟡',
                'completed' => '🟢',
                default => '⚪'
            };
            $name = match($cat) {
                'not_submitted' => 'Not Submitted',
                'in_progress' => 'In Progress',
                'completed' => 'Completed',
                default => 'Unknown'
            };
            $this->line("  {$icon} {$name}: {$count}");
        }

        $this->newLine();
        $this->line("Total requests: " . $allRequests->count());

        return 0;
    }
}
