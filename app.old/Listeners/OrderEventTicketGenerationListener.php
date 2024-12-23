<?php

namespace App\Listeners;

use App\Actions\Order\AttachEventTicketCodeToOrderItemAction;
use App\Events\OrderEventTicketGenerationEvent;

class OrderEventTicketGenerationListener
{
    public function __construct()
    {
    }

    public function handle(OrderEventTicketGenerationEvent $event)
    {
        AttachEventTicketCodeToOrderItemAction::make()->handle($event->order);
    }
}
