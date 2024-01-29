<?php

namespace App\Services;

use App\Actions\User\UpdateOrCreateUserAction;
use App\Events\OrderEventTicketGenerationEvent;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Transaction;
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
        $user = UpdateOrCreateUserAction::make()->handle($cartData);
        $cartItems = CartItem::whereIn('id', $cartData['cart_item'])->get();
        $order = Order::create([
            'user_id' => $user->id,
            'price' => 0,
        ]);
        $orderPrice = 0;

        foreach ($cartItems as $cartItem) {
            $eventTicket = $cartItem->eventTicket;
            if ($cartItem->type === 'TICKET' && ($eventTicket->stock === 0 || $eventTicket->stock < $cartItem->qty)) {
                throw new \Exception('There is not enough stock for this ticket.');
            }
            if ($cartItem->type === 'TICKET' && ($eventTicket->isAvailable() === false)) {
                throw new \Exception('This ticket is sold out.');
            }
            if ($cartItem->type === 'TICKET') {
                $eventTicket->stock = $eventTicket->stock - $cartItem->qty;
                $eventTicket->save();
            }

            $orderItem = Order\OrderItem::create([
                'qty' => $cartItem->qty,
                'single_price' => $cartItem->single_price,
                'row_price' => $cartItem->row_price,
                'cart_item_id' => $cartItem->id,
                'event_ticket_id' => $eventTicket?->id,
                'type' => $cartItem->type,
            ]);
            $orderPrice += $orderItem->row_price;
            $order->orderItems()->save($orderItem);
        }
        $order->price = $orderPrice;
        $transaction = $this->createTransaction($order, $cartData);
        $order->transaction()->associate($transaction);
        $order->save();
        OrderEventTicketGenerationEvent::dispatch($order);

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
            'reference' => Str::upper(Str::random(Transaction::REFERENCE_LENGTH)),
            'amount' => $order->price,
            'state' => Transaction::STATE_INIT
        ]);
    }
}
