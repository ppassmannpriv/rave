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
        $schedule->command('payment_methods:paypal_ff:scan-report')
            ->everyFifteenMinutes()
            ->emailOutputOnFailure([env('SYSADMIN')])
            ->appendOutputTo('storage/logs/paypal_ff_scan-report.log');

        $schedule->command('orders:finish')
            ->everyTenMinutes()
            ->emailOutputOnFailure([env('SYSADMIN')])
            ->appendOutputTo('storage/logs/order-successful.log');
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
