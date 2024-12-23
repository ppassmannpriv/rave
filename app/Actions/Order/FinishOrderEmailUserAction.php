<?php

namespace App\Actions\Order;

use App\Mail\OrderSuccessful;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FinishOrderEmailUserAction
{
    public function handle(Order $order): void
    {
        if (Mail::to($order->user)->send(new OrderSuccessful($order))) {
            Log::info('Order Success mail sent to ' . $order->user->email);
        }

        $order->transaction->state = Transaction::STATE_SUCCESS;
        $order->transaction->save();
        Log::info('Transaction state changed to ' . Transaction::STATE_SUCCESS);

        $order->status = Order::STATUS_CLOSED;
        $order->save();
        Log::info('Order state changed to ' . Order::STATUS_CLOSED);
    }
}
