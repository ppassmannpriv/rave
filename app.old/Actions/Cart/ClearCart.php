<?php

namespace App\Actions\Cart;

use App\Models\EventTicket;
use Lorisleiva\Actions\Concerns\AsAction;

class ClearCart
{
    use AsAction;

    public function handle(EventTicket $eventTicket): void
    {
        if ($cart === null) {
            throw new \Exception('Cart could not be loaded!');
        }
    }
}
