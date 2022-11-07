<?php

namespace App\Console\Commands;

use App\Actions\EventTicketCode\CleanUpOrphans;
use App\Models\EventTicket;
use Illuminate\Console\Command;

class CheckStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'event-ticket:check-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check stock and output how many have been sold.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Checking.');
        $eventTickets = EventTicket::all();
        foreach ($eventTickets as $eventTicket) {
            $soldCodes = $eventTicket->eventTicketCodes()->whereNotNull('order_item_id')->get();
            $this->info($eventTicket->event->name . ' has sold ' . $soldCodes->count() . ' tickets.');
            $remaining = $eventTicket->cap - $soldCodes->count();
            if ($remaining < 0) {
                $remaining = 0;
            }
            $eventTicket->stock = $remaining;
            $eventTicket->save();
            $this->info('Correcting stock for ticket. Set it to ' . $eventTicket->stock);
        }
        $this->info('Done.');

        return 0;
    }
}
