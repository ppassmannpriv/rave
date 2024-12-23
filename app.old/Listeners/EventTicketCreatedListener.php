<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\EventTicketCreatedEvent;
use App\Actions\EventTicket\CreateCodesForNewEventTicket;

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
