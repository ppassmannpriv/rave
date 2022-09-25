<?php

namespace App\Actions\Cart;

use App\Services\OrderService;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrderFromCart
{
    use AsAction;

    public function handle(array $cartData): void
    {
        $orderService = \App::make(OrderService::class);
        if ($orderService === null) {
            throw new \Exception('Order service could not be loaded!');
        }
        $orderService->makeFromCart($cartData);
    }
}
