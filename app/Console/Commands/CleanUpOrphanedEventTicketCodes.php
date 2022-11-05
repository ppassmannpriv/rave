<?php

namespace App\Console\Commands;

use App\Actions\EventTicketCode\CleanUpOrphans;
use Illuminate\Console\Command;

class CleanUpOrphanedEventTicketCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'event-ticket-codes:clean-up-orphans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up orphaned event ticket codes, so these can be bought again.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting to clean up.');
        CleanUpOrphans::make()->handle();
        $this->info('Done.');

        return 0;
    }
}
