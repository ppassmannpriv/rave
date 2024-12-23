<?php

namespace App\Actions\Bookkeeping;

use App\Models\Event;
use App\Models\Order;
use App\Models\Order\OrderItem;
use Lorisleiva\Actions\Concerns\AsAction;

class GetIncomePerPaymentMethod
{
    use AsAction;

    public function handle(Event $event): array
    {
        $eventTicketIds = $event->eventTickets->pluck('id');
        $orderItems = OrderItem::whereIn('event_ticket_id', $eventTicketIds)->get();
        $orders = $orderItems->map(fn (OrderItem $orderItem) => $orderItem?->order?->status === Order::STATUS_CLOSED ? $orderItem->order : null)->unique();
        $groupedBy = $orders->groupBy('transaction.paymentMethod.name');
        $sums = [];
        foreach ($groupedBy as $paymentMethodName => $orders) {
            $sum = $orders->sum('price');
            if ($sum > 0) {
                $sums[$paymentMethodName] = $orders->sum('price');
            }
        }

        return $sums;
    }
}
