<?php

namespace App\Actions\Order;

use App\Mail\OrderCancelled;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class CancelOrderAction
{
    use AsAction;

    public function handle(Order $order): void
    {
        if (Mail::to($order->user)->send(new OrderCancelled($order))) {
            Log::info('Order Cancellation mail sent to ' . $order->user->email);
        }

        $order->transaction->state = Transaction::STATE_CANCEL;
        $order->transaction->save();
        Log::info('Transaction state changed to ' . Transaction::STATE_CANCEL);

        $order->status = Order::STATUS_CANCELLED;
        $order->save();
        Log::info('Order state changed to ' . Order::STATUS_CANCELLED);
    }
}
