<?php

namespace App\Actions\Cart;

use App\Models\EventTicket;
use App\Services\CartService;
use Lorisleiva\Actions\Concerns\AsAction;

class AddTicketToCart
{
    use AsAction;

    public function handle(EventTicket $eventTicket): void
    {
        $cartService = \App::make(CartService::class);
        if ($cartService === null) {
            throw new \Exception('Cart Service could not be loaded!');
        }
        $cartService->add($eventTicket);
    }
}
