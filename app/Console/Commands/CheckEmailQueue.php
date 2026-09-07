<?php

namespace App\Console\Commands;

use App\Models\NotificationLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckEmailQueue extends Command
{
    protected $signature = 'check:email-queue';
    protected $description = 'Check email queue status and notification logs';

    public function handle()
    {
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📧 EMAIL QUEUE STATUS');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        // Check jobs queue
        $pendingJobs = DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->count();

        $this->info('Queue Status:');
        $this->line('  Pending Jobs: ' . ($pendingJobs > 0 ? "⚠️  {$pendingJobs}" : "✓ 0"));
        $this->line('  Failed Jobs: ' . ($failedJobs > 0 ? "❌ {$failedJobs}" : "✓ 0"));
        $this->newLine();

        if ($pendingJobs > 0) {
            $this->warn('⚠️  Ada job yang tertunda di queue!');
            $this->info('Jalankan queue worker dengan: php artisan queue:work');
            $this->newLine();
        }

        // Check notification logs
        $this->info('Recent Notification Logs:');
        $logs = NotificationLog::orderBy('created_at', 'desc')->take(10)->get();

        if ($logs->isEmpty()) {
            $this->comment('  Belum ada log notifikasi');
        } else {
            foreach ($logs as $log) {
                $statusIcon = match($log->status) {
                    'SENT' => '✓',
                    'FAILED' => '❌',
                    'PENDING' => '⏳',
                    default => '?'
                };

                $this->line("  {$statusIcon} [{$log->type}] to {$log->recipient}");
                $this->line("     Date: {$log->created_at->format('d M Y H:i:s')} | Status: {$log->status}");
                if ($log->error_message) {
                    $this->error("     Error: {$log->error_message}");
                }
                $this->newLine();
            }
        }

        // Check mail configuration
        $this->info('Mail Configuration:');
        $this->line('  Mailer: ' . config('mail.default'));
        $this->line('  Host: ' . config('mail.mailers.smtp.host'));
        $this->line('  Port: ' . config('mail.mailers.smtp.port'));
        $this->line('  From: ' . config('mail.from.address'));
        $this->newLine();

        return 0;
    }
}
