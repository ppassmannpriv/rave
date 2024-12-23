<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\TransactionSavedEvent;
use App\Models\Transaction;
use App\Models\Order;

class TransactionPaidListener
{
    public function __construct()
    {
    }

    public function handle(TransactionSavedEvent $event)
    {
        if ($event->transaction->state === Transaction::STATE_PAID &&
            !in_array($event->transaction->order->status, [
                Order::STATUS_CLOSED,
                Order::STATUS_CANCELLED,
                Order::STATUS_REFUNDED,
                Order::STATUS_PAID
            ])
        ) {
            $event->transaction->order->status = Order::STATUS_PAID;
            $event->transaction->order->save();
        }
    }
}
