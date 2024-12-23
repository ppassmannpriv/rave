<?php

namespace App\Actions\Cart;

use App\Models\Order;
use App\Services\OrderService;

class CreateOrderFromCart
{
    public function handle(array $cartData): Order
    {
        $orderService = \App::make(OrderService::class);
        if ($orderService === null) {
            throw new \Exception('Order service could not be loaded!');
        }
        return $orderService->makeFromCart($cartData);
    }
}
