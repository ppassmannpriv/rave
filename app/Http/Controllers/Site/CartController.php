<?php

namespace App\Http\Controllers\Site;

use App\Actions\Cart\AddTicketToCart;
use App\Http\Requests\Site\AddToCartRequest;
use App\Models\EventTicket;

class CartController extends WebController
{
    public function index() {
        $this->respond('cart.index');
    }

    public function addToCart(AddToCartRequest $request) {
        AddTicketToCart::make()->handle(EventTicket::find($request->event_ticket_id));
        return \Redirect::to('/cart');
    }
}
