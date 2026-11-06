<?php

namespace App\Http\Controllers\Site;

use App\Actions\Cart\AddTicketToCart;
use App\Actions\Cart\CreateOrderFromCart;
use App\Actions\Cart\RemoveTicketFromCart;
use App\Actions\Payment\PayPal\StartPayPalPayment;
use App\Exceptions\Cart\SoldOutException;
use App\Http\Requests\Site\AddToCartRequest;
use App\Http\Requests\Site\OrderCartRequest;
use App\Http\Requests\Site\RemoveFromCartRequest;
use App\Mail\OrderCreated;
use App\Mail\OrderCreatedNotification;
use App\Models\EventTicket;
use App\Models\PaymentMethod;
use App\Models\PaymentMethod\PayPal;
use App\Services\CartService;
use App\Services\PayPal\PayPalApiException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CartController extends WebController
{
    public function index()
    {
        return $this->respond('cart.index', ['paymentMethods' => PaymentMethod::selectableBy(auth()->user())->get(), 'siteType' => 'checkout']);
    }

    public function addToCart(AddToCartRequest $request)
    {
        try {
            AddTicketToCart::make()->handle(EventTicket::find($request->event_ticket_id), $request->qty);
        } catch (SoldOutException $soldOutException) {
            return \Redirect::to('/cart')->with('error', $soldOutException->getMessage());
        }

        return \Redirect::to('/cart');
    }

    public function removeFromCart(RemoveFromCartRequest $request)
    {
        RemoveTicketFromCart::make()->handle($request->id);

        return \Redirect::to('/cart');
    }

    public function orderCart(OrderCartRequest $request)
    {
        try {
            $order = CreateOrderFromCart::make()->handle($request->all());
        } catch (SoldOutException $soldOutException) {
            return \Redirect::to('/cart')->with('error', $soldOutException->getMessage());
        }
        session()->put('lastOrder', $order);
        $this->cartService->clear();
        Mail::to($order->user)->send(new OrderCreated($order));
        Mail::to(config('mail.from.address'))->send(new OrderCreatedNotification($order));
        if ($order->transaction->paymentMethod->alias === PayPal::ALIAS) {
            try {
                return \Redirect::away(StartPayPalPayment::run($order->transaction));
            } catch (PayPalApiException $exception) {
                Log::error($exception, ['order' => $order->id]);

                return \Redirect::to('/cart/success')->with('warning', 'We could not reach PayPal. Your order is saved; please contact us to complete the payment.');
            }
        }

        return \Redirect::to('/cart/success');
    }

    public function success()
    {
        $order = session()->get('lastOrder');
        if ($order === null) {
            return \Redirect::to('/')->with('warning', 'No order made, you cannot see an empty success page.');
        }
        session()->forget('lastOrder');
        session()->forget(CartService::DEFAULT_INSTANCE);

        return $this->respond('cart.success', ['order' => $order, 'siteType' => 'checkout']);
    }
}
