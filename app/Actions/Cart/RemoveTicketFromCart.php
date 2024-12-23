<?php

namespace App\Actions\Cart;

use App\Services\CartService;

class RemoveTicketFromCart
{
    public function handle($eventTicketId): void
    {
        $cartService = \App::make(CartService::class);
        if ($cartService === null) {
            throw new \Exception('Cart could not be loaded!');
        }
        $cartService->remove($eventTicketId);
    }
}
