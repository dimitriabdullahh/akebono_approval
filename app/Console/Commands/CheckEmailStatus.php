<?php

namespace App\Console\Commands;

use App\Models\NotificationLog;
use App\Models\DocumentRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckEmailStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:check
                            {--request= : Filter by document request ID}
                            {--type= : Filter by notification type (REQUEST_LETTER, DOCS_SUBMITTED, APPROVAL_COMPLETED, REVISION_REMINDER)}
                            {--limit=10 : Number of records to display}
                            {--failed : Show only failed notifications}
                            {--today : Show only today\'s notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check email notification status from notification logs';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $query = NotificationLog::query()->with(['documentRequest.supplier']);

        // Apply filters
        if ($requestId = $this->option('request')) {
            $query->where('document_request_id', $requestId);
        }

        if ($type = $this->option('type')) {
            $query->where('type', strtoupper($type));
        }

        if ($this->option('failed')) {
            $query->where('status', 'FAILED');
        }

        if ($this->option('today')) {
            $query->whereDate('created_at', today());
        }

        $limit = (int) $this->option('limit');
        $notifications = $query->orderBy('created_at', 'desc')->limit($limit)->get();

        if ($notifications->isEmpty()) {
            $this->info('No email notifications found with the given criteria.');
            return 0;
        }

        $this->info("Found {$notifications->count()} email notification(s):\n");

        // Display as table
        $this->table(
            ['ID', 'Type', 'Supplier', 'Request #', 'Recipient', 'Status', 'Sent At'],
            $notifications->map(function ($log) {
                $supplier = $log->documentRequest && $log->documentRequest->supplier 
                    ? $log->documentRequest->supplier->name 
                    : '-';
                
                $requestNumber = $log->documentRequest 
                    ? $log->documentRequest->request_number 
                    : '-';

                return [
                    $log->id,
                    $this->formatType($log->type),
                    $supplier,
                    $requestNumber,
                    $log->recipient,
                    $this->formatStatus($log->status),
                    $log->sent_at ? $log->sent_at->format('Y-m-d H:i:s') : '-',
                ];
            })
        );

        // Show statistics
        $this->newLine();
        $this->showStatistics();

        // Option to show details
        if ($this->confirm('Do you want to see details of a specific notification?', false)) {
            $id = $this->ask('Enter notification log ID');
            $this->showDetails($id);
        }

        return 0;
    }

    /**
     * Format notification type for display
     */
    private function formatType(string $type): string
    {
        $types = [
            'REQUEST_LETTER' => '📤 Request Letter',
            'DOCS_SUBMITTED' => '📄 Docs Submitted',
            'APPROVAL_COMPLETED' => '✅ Approval Done',
            'REVISION_REMINDER' => '⏰ Revision Reminder',
        ];

        return $types[$type] ?? $type;
    }

    /**
     * Format status with color
     */
    private function formatStatus(string $status): string
    {
        return match($status) {
            'SENT' => "<fg=green>✓ Sent</>",
            'FAILED' => "<fg=red>✗ Failed</>",
            default => $status,
        };
    }

    /**
     * Show email statistics
     */
    private function showStatistics(): void
    {
        $this->info('=== Email Statistics ===');

        // Total emails
        $total = NotificationLog::count();
        $this->line("Total emails logged: <fg=cyan>{$total}</>");

        // By status
        $byStatus = NotificationLog::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        $sent = $byStatus->get('SENT', 0);
        $failed = $byStatus->get('FAILED', 0);

        $this->line("Sent: <fg=green>{$sent}</>");
        $this->line("Failed: <fg=red>{$failed}</>");

        // By type
        $this->newLine();
        $this->line('By Type:');
        $byType = NotificationLog::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get();

        foreach ($byType as $type) {
            $this->line("  {$this->formatType($type->type)}: {$type->count}");
        }

        // Today's emails
        $today = NotificationLog::whereDate('created_at', today())->count();
        $this->newLine();
        $this->line("Today's emails: <fg=cyan>{$today}</>");
    }

    /**
     * Show detailed information about a notification
     */
    private function showDetails($id): void
    {
        $log = NotificationLog::with(['documentRequest.supplier'])->find($id);

        if (!$log) {
            $this->error("Notification log ID {$id} not found.");
            return;
        }

        $this->newLine();
        $this->info('=== Notification Details ===');
        $this->line("ID: {$log->id}");
        $this->line("Type: {$this->formatType($log->type)}");
        $this->line("Status: {$this->formatStatus($log->status)}");
        $this->line("Recipient: {$log->recipient}");
        $this->line("Subject: {$log->subject}");
        
        if ($log->documentRequest) {
            $this->newLine();
            $this->line("Request Number: {$log->documentRequest->request_number}");
            $this->line("Request Status: {$log->documentRequest->status}");
            
            if ($log->documentRequest->supplier) {
                $supplier = $log->documentRequest->supplier;
                $this->line("Supplier: {$supplier->name} ({$supplier->code})");
            }
        }

        $this->newLine();
        $this->line("Created At: {$log->created_at->format('Y-m-d H:i:s')}");
        
        if ($log->sent_at) {
            $this->line("Sent At: {$log->sent_at->format('Y-m-d H:i:s')}");
        }

        if ($log->error_message) {
            $this->newLine();
            $this->error("Error Message:");
            $this->line($log->error_message);
        }
    }
}
