<?php

namespace App\Actions\Cart;

use App\Models\EventTicket;

class ClearCart
{
    public function handle(EventTicket $eventTicket): void
    {
        if ($cart === null) {
            throw new \Exception('Cart could not be loaded!');
        }
    }
}
