<?php

namespace App\Listeners;

use App\Actions\Order\AttachEventTicketCodeToOrderItemAction;
use App\Events\OrderEventTicketGenerationEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

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
