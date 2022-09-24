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
            throw new \Exception('Cart could not be loaded!');
        }
        $payload = (object)[
            'id' => $eventTicket->id . '-' . mt_rand(10000000, 99999999),
            'eventTicket' => $eventTicket
        ];
        $cartService->add($payload);
    }
}
