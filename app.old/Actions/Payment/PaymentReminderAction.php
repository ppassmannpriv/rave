<?php

namespace App\Actions\Payment;

use App\Mail\PaymentReminder;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class PaymentReminderAction {
    use AsAction;

    public function handle(Order $order)
    {
        if ($order->status === Order::STATUS_INITIALIZED
            && $order->transaction->state === Transaction::STATE_INIT) {
            Mail::to($order->user)->send(new PaymentReminder($order));
        }
    }
}
