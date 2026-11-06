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
            ->withoutOverlapping(10)
            ->onOneServer()
            ->emailOutputOnFailure([config('rave.sysadmin')])
            ->appendOutputTo('storage/logs/paypal_ff_scan-report.log');

        $schedule->command('orders:finish')
            ->everyTenMinutes()
            ->withoutOverlapping(10)
            ->onOneServer()
            ->emailOutputOnFailure([config('rave.sysadmin')])
            ->appendOutputTo('storage/logs/order-successful.log');

        $schedule->command('orders:payment-check')
            ->everyFiveMinutes()
            ->withoutOverlapping(10)
            ->onOneServer()
            ->emailOutputOnFailure([config('rave.sysadmin')])
            ->appendOutputTo('storage/logs/payment-reminders.log');

        $schedule->command('event-ticket-codes:clean-up-orphans')
            ->hourly()
            ->withoutOverlapping(30)
            ->onOneServer()
            ->emailOutputOnFailure([config('rave.sysadmin')])
            ->appendOutputTo('storage/logs/clean-up-orphaned-event-ticket-codes.log');
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
