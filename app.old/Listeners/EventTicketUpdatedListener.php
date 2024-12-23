<?php

namespace App\Listeners;

use App\Actions\EventTicket\CreateCodesForStockUpdateOnEventAction;
use App\Events\EventTicketUpdatedEvent;

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
