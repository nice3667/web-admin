<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // User data sync jobs
        $schedule->command('sync:all-users')
                 ->everyThirtyMinutes()
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/user-sync.log'));

        // Individual backup sync jobs
        $schedule->command('sync:ham-data')
                 ->hourlyAt(5)
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/ham-sync.log'));

        $schedule->command('sync:kantapong-data')
                 ->hourlyAt(15)
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/kantapong-sync.log'));

        $schedule->command('sync:janischa-data')
                 ->hourlyAt(25)
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/janischa-sync.log'));
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
