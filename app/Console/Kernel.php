<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Kirim pengingat revisi setiap hari pada jam 08:00 WIB
        $schedule->call(function () {
            $service = app(\App\Services\ReviewApprovalService::class);
            $revisionDocs = $service->getRevisionDocumentsNearingDeadline();

            foreach ($revisionDocs as $doc) {
                \App\Jobs\RevisionReminderJob::dispatch($doc->documentRequest);
            }
        })->dailyAt('08:00');

        // Check bundle submission reminder setiap 1 menit
        // Bundle-based: Reminder untuk setiap part/bundle, berhenti hanya jika SUBMITTED
        // Ini sudah cover semua skenario: belum upload maupun sudah upload tapi belum submit
        $schedule->command('reminder:check-bundle-submission')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground();

        // Check no submission reminder setiap 1 menit
        // Request-based: Reminder untuk supplier yang belum mengirim dokumen sama sekali
        // Eskalasi dari PIC -> Supervisor -> Head Dept -> GM -> Director
        $schedule->command('reminder:check-no-submission')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
