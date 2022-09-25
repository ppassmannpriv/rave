<?php

namespace App\Services;

use App\Events\OrderEventTicketGenerationEvent;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Str;

/**
 * @mixin \Eloquent
 */
class OrderService {
    /**
     * @throws \Exception
     */
    public function makeFromCart(array $cartData): Order
    {
        $user = User::where('email', '=', $cartData['email'])->first();
        if ($user === null) {
            $user = User::create([
                'name'           => $cartData['firstname'] . ' ' . $cartData['lastname'],
                'email'          => $cartData['email'],
                'password'       => bcrypt(Str::random(10)),
                'remember_token' => null,
            ]);
        }
        $cartItem = CartItem::find($cartData['cart_item']);

        $eventTicket = $cartItem->eventTicket;
        if ($eventTicket->stock === 0) {
            throw new \Exception('There is not enough stock for this ticket.');
        }
        $eventTicket->stock = $eventTicket->stock - 1;
        $eventTicket->save();

        $orderItem = Order\OrderItem::create([
            'qty' => $cartItem->qty,
            'single_price' => $cartItem->single_price,
            'row_price' => $cartItem->row_price,
            'cart_item_id' => $cartItem->id,
            'event_ticket_id' => $eventTicket->id
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'price' => $orderItem->row_price,
        ]);
        $order->orderItems()->save($orderItem);
        $transaction = $this->createTransaction($order, $cartData);
        $order->transaction()->associate($transaction);
        OrderEventTicketGenerationEvent::dispatch($order);
        $order->save();

        return $order;
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
            'reference' => Str::upper(Str::random(10)),
            'amount' => $order->price,
            'state' => Transaction::STATE_INIT
        ]);
    }
}
