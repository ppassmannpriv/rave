<?php

namespace App\Http\Controllers\Site;

use App\Actions\Cart\AddTicketToCart;
use App\Actions\Cart\RemoveTicketFromCart;
use App\Http\Requests\Site\AddToCartRequest;
use App\Http\Requests\Site\RemoveFromCartRequest;
use App\Models\EventTicket;

class CartController extends WebController
{
    public function index() {
        return $this->respond('cart.index');
    }

    public function addToCart(AddToCartRequest $request) {
        AddTicketToCart::make()->handle(EventTicket::find($request->event_ticket_id));
        return \Redirect::to('/cart');
    }

    public function removeFromCart(RemoveFromCartRequest $request) {
        RemoveTicketFromCart::make()->handle($request->id);
        return \Redirect::to('/cart');
    }
}
