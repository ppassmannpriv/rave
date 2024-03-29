<?php

namespace App\Actions\Order;

use App\Mail\OrderCancelled;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class AbortOrderAction
{
    use AsAction;

    public function handle(Order $order): void
    {
        $order->transaction->state = Transaction::STATE_CANCEL;
        $order->transaction->save();
        Log::info('Transaction state changed to ' . Transaction::STATE_CANCEL);

        $order->status = Order::STATUS_CANCELLED;
        $order->save();
        Log::info('Order state changed to ' . Order::STATUS_CANCELLED);

        foreach ($order->orderItems as $orderItem) {
            if ($orderItem->type !== 'TICKET') {
                continue;
            }
            $eventTicket = $orderItem->eventTicket;
            $eventTicket->stock += $orderItem->qty;
            $eventTicket->save();
            Log::info('Set Event Ticket (' . $eventTicket->id . ') stock back to: ' . $eventTicket->stock);
            foreach ($orderItem->eventTicketCodes as $eventTicketCode) {
                $eventTicketCode->orderItem()->disassociate();
                $eventTicketCode->save();
                Log::info('Cleared Event Ticket Code: ' . $eventTicketCode->code);
            }
        }
    }
}
