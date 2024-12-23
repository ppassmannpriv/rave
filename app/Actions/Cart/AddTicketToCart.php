<?php

namespace App\Actions\Cart;

use App\Exceptions\Cart\SoldOutException;
use App\Models\EventTicket;
use App\Models\Merchandise;
use App\Services\CartService;

class AddTicketToCart
{
    public function handle(EventTicket|Merchandise $eventTicket, int $qty = 1): void
    {
        $cartService = \App::make(CartService::class);
        if ($cartService === null) {
            throw new \Exception('Cart Service could not be loaded!');
        }
        if ($eventTicket->isAvailable() === false) {
            throw new SoldOutException('Ticket is sold out.');
        }
        $cartService->add($eventTicket, $qty);
    }
}
