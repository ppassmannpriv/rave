<?php

namespace App\Actions\Cart;

use App\Models\EventTicket;
use App\Services\CartService;
use Lorisleiva\Actions\Concerns\AsAction;

class RemoveTicketFromCart
{
    use AsAction;

    public function handle($eventTicketId): void
    {
        $cartService = \App::make(CartService::class);
        if ($cartService === null) {
            throw new \Exception('Cart could not be loaded!');
        }
        $payload = (object)[
            'id' => $eventTicketId,
        ];
        $cartService->remove($payload);
    }
}
