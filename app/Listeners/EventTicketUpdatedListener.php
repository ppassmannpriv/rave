<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\EventTicketUpdatedEvent;
use App\Actions\EventTicket\CreateCodesForStockUpdateOnEventAction;

class EventTicketUpdatedListener
{
    public function __construct()
    {
    }

    public function handle(EventTicketUpdatedEvent $event)
    {
        CreateCodesForStockUpdateOnEventAction::make()->handle($event->eventTicket);
    }
}
