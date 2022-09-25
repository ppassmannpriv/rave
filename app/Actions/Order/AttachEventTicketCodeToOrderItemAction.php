<?php

namespace App\Actions\Order;

use App\Models\Order;

use Lorisleiva\Actions\Concerns\AsAction;

class AttachEventTicketCodeToOrderItemAction
{
    use AsAction;

    public function handle(Order $order): void
    {
        foreach ($order->orderItems as $orderItem) {
            $eventTicketCode = $orderItem->eventTicket->eventTicketCodes()->whereNull('order_item_id')->first();
            if ($eventTicketCode === null) {
                throw new \Exception('There are no more event ticket codes available.');
            }
            $eventTicketCode->orderItem()->associate($orderItem);
            $eventTicketCode->save();
        }
    }
}
