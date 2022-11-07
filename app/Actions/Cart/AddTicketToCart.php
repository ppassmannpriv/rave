<?php

namespace App\Actions\Cart;

use App\Exceptions\Cart\SoldOutException;
use App\Models\EventTicket;
use App\Services\CartService;
use Lorisleiva\Actions\Concerns\AsAction;

class AddTicketToCart
{
    use AsAction;

    public function handle(EventTicket $eventTicket, int $qty = 1): void
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
