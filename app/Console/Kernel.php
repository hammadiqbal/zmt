<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('status:checker')->everyFiveMinutes();
        // $schedule->command('service:scheduling')->everyMinute();
        // $schedule->command('service:bookingstatus')->everyMinute();
        // $schedule->command('service:end')->everyMinute();
        $schedule->command('brand:brand-expiration')->everyFiveMinutes();
        $schedule->command('reports:process')->everyMinute();
        $schedule->command('reports:process-pdf')->everyMinute(); // Dedicated PDF generation with 5-minute timeout
        // Stock monitoring alerts - run once daily
        $schedule->command('stock:monitor')->daily();
    }
    

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
