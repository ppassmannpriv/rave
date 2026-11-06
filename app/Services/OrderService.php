<?php

namespace App\Services;

use App\Events\OrderEventTicketGenerationEvent;
use App\Exceptions\Cart\SoldOutException;
use App\Models\Cart;
use App\Models\EventTicket;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\User;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @mixin \Eloquent
 */
class OrderService
{
    /**
     * Creates one order with one order item per cart item. Everything happens in a single
     * transaction with the tickets locked, so a sold-out ticket or a missing ticket code rolls
     * the whole order back instead of leaving stock decremented for an order that does not exist.
     *
     * @throws SoldOutException
     * @throws \Exception
     */
    public function makeFromCart(array $cartData, Cart $cart): Order
    {
        $cartItems = $cart->cartItems()->get();
        if ($cartItems->isEmpty()) {
            throw new \Exception('The cart is empty.');
        }

        return DB::transaction(function () use ($cartData, $cartItems) {
            $user = User::where('email', '=', $cartData['email'])->first();
            if ($user === null) {
                $user = User::create([
                    'name' => $cartData['firstname'].' '.$cartData['lastname'],
                    'email' => $cartData['email'],
                    'password' => bcrypt(Str::random(10)),
                    'remember_token' => null,
                ]);
            }

            $orderItems = [];
            $total = Money::fromCents(0);
            foreach ($cartItems as $cartItem) {
                $eventTicket = EventTicket::lockForUpdate()->find($cartItem->event_ticket_id);
                if ($eventTicket === null || $eventTicket->isAvailable() === false || $eventTicket->hasStockFor($cartItem->qty) === false) {
                    throw new SoldOutException('There are not enough tickets left for your cart. Please lower the quantity or remove the sold out ticket.');
                }
                $eventTicket->stock = $eventTicket->stock - $cartItem->qty;
                $eventTicket->save();

                $orderItems[] = Order\OrderItem::create([
                    'qty' => $cartItem->qty,
                    'single_price' => $cartItem->single_price,
                    'row_price' => $cartItem->row_price,
                    'cart_item_id' => $cartItem->id,
                    'event_ticket_id' => $eventTicket->id,
                ]);
                $total = $total->add(Money::fromDecimalString($cartItem->row_price));
            }

            $order = Order::create([
                'user_id' => $user->id,
                'price' => $total->toDecimalString(),
            ]);
            $order->orderItems()->saveMany($orderItems);
            $transaction = $this->createTransaction($order, $cartData);
            $order->transaction()->associate($transaction);
            OrderEventTicketGenerationEvent::dispatch($order);
            $order->save();

            return $order;
        });
    }

    /**
     * @throws \Exception
     */
    private function createTransaction(Order $order, array $cartData): Transaction
    {
        $paymentMethod = PaymentMethod::where('alias', '=', $cartData['payment_method'])->first();
        if ($paymentMethod === null) {
            throw new \Exception('Payment method not found.');
        }

        return Transaction::create([
            'payment_method_id' => $paymentMethod->id,
            'order_id' => $order->id,
            'reference' => Str::upper(Str::random(Transaction::REFERENCE_LENGTH)),
            'amount' => $order->price,
            'state' => Transaction::STATE_INIT,
        ]);
    }
}
