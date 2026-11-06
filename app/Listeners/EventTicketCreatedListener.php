<?php

namespace App\Listeners;

use App\Actions\EventTicket\CreateCodesForNewEventTicket;
use App\Events\EventTicketCreatedEvent;

class EventTicketCreatedListener
{
    public function __construct()
    {
    }

    public function handle(EventTicketCreatedEvent $event)
    {
        CreateCodesForNewEventTicket::make()->handle($event->eventTicket);
    }
}
