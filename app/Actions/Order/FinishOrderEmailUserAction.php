<?php

namespace App\Actions\Order;

use App\Mail\OrderCreated;
use App\Mail\OrderSuccessful;
use App\Models\Order;

use App\Models\Transaction;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class FinishOrderEmailUserAction
{
    use AsAction;

    public function handle(Order $order): void
    {
        Mail::to($order->user)->send(new OrderSuccessful($order));

        //$order->transaction->state = Transaction::STATE_SUCCESS;
        //$order->transaction->save();
    }
}
