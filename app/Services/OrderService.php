<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Support\Collection;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Str;

class OrderService {
    public function makeFromCart(array $cartData)
    {
        $order = Order::create([
            ''
        ]);
    }

    private function createTransaction(Order $order)
    {
        Transaction::create([
            'payment_method_id' => null,
            'order_id' => $order->id,
            'reference' => Str::upper(Str::random(10)),
            'amount' => $order->price,
            'state' => Transaction::STATE_INIT
        ]);
    }
}
