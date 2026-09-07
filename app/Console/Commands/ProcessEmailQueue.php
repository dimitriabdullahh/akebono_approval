<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessEmailQueue extends Command
{
    protected $signature = 'process:email-queue {--all : Process all pending jobs}';
    protected $description = 'Process email queue (one job or all pending jobs)';

    public function handle()
    {
        $pendingJobs = DB::table('jobs')->count();

        if ($pendingJobs === 0) {
            $this->info('✓ No pending jobs in queue');
            return 0;
        }

        $this->info("Found {$pendingJobs} pending job(s)");
        $this->newLine();

        if ($this->option('all')) {
            $this->info('Processing all pending jobs...');
            $this->call('queue:work', [
                '--stop-when-empty' => true,
                '--tries' => 3,
            ]);
        } else {
            $this->info('Processing one job...');
            $this->call('queue:work', [
                '--once' => true,
                '--tries' => 3,
            ]);
        }

        $this->newLine();
        $this->info('✓ Done!');
        $this->newLine();

        // Show updated status
        $this->call('check:email-queue');

        return 0;
    }
}
