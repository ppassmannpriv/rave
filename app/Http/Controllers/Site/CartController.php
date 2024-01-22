<?php

namespace App\Http\Controllers\Site;

use App\Exceptions\Cart\SoldOutException;
use App\Mail\OrderCreatedNotification;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderCreated;
use App\Actions\Cart\AddTicketToCart;
use App\Actions\Cart\CreateOrderFromCart;
use App\Actions\Cart\RemoveTicketFromCart;
use App\Http\Requests\Site\AddToCartRequest;
use App\Http\Requests\Site\RemoveFromCartRequest;
use App\Http\Requests\Site\OrderCartRequest;
use App\Models\EventTicket;
use App\Models\PaymentMethod;
use App\Services\CartService;

class CartController extends WebController
{
    public function index() {
        return $this->respond('cart.index', ['paymentMethods' => PaymentMethod::where('active', '=', true)->get(), 'siteType' => 'checkout']);
    }

    public function addToCart(AddToCartRequest $request) {
        try {
            AddTicketToCart::make()->handle(EventTicket::find($request->event_ticket_id), $request->qty);
        } catch (SoldOutException $soldOutException) {
            return \Redirect::to('/cart')->with($soldOutException->getMessage());
        }
        return \Redirect::to('/cart');
    }

    public function removeFromCart(RemoveFromCartRequest $request) {
        RemoveTicketFromCart::make()->handle($request->id);
        return \Redirect::to('/cart');
    }

    public function orderCart(OrderCartRequest $request) {
        $order = CreateOrderFromCart::make()->handle($request->all());
        $transaction = $order->transaction;
        $paymentMethod = $transaction->paymentMethod->model();

        session()->put('lastOrder', $order);
        Mail::to($order->user)->send(new OrderCreated($order, $paymentMethod::ALIAS));
        Mail::to(env('MAIL_FROM_ADDRESS'))->send(new OrderCreatedNotification($order));
        return $paymentMethod->handle($transaction);

        return \Redirect::to('/cart/success');
    }

    public function success() {
        $order = session()->get('lastOrder');
        if ($order === null) {
            return \Redirect::to('/')->with('warning', 'No order made, you cannot see an empty success page.');
        }
        session()->forget('lastOrder');
        session()->forget(CartService::DEFAULT_INSTANCE);
        return $this->respond('cart.success', ['order' => $order, 'siteType' => 'checkout']);
    }
}
