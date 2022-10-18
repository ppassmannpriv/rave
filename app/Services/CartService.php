<?php

namespace App\Services;

use App\Contracts\Cart;
use App\Models\CartItem;
use Illuminate\Support\Collection;
use Illuminate\Session\SessionManager;

class CartService implements Cart {
    const MINIMUM_QUANTITY = 1;
    const TTL = 900;
    const DEFAULT_INSTANCE = 'cartId';

    protected SessionManager $session;
    protected $instance;

    /**
     * Constructs a new cart object.
     *
     * @param SessionManager $session
     */
    public function __construct(SessionManager $session)
    {
        $this->session = $session;
    }

    /**
     * Clears the cart.
     *
     * @return void
     */
    public function clear(): void
    {
        $this->session->forget(self::DEFAULT_INSTANCE);
    }

    public function content()
    {
        return $this->getCart();
    }

    protected function getCart(): \App\Models\Cart
    {
        $sessionCartId = $this->session->get(self::DEFAULT_INSTANCE);
        if ($sessionCartId === null) {
            $this->clear();
            $cart = $this->loadDatabaseCart();
            $this->session->put(self::DEFAULT_INSTANCE, $cart->id);
            return $cart;
        }
        return $this->loadDatabaseCart($sessionCartId);
    }

    public function add($eventTicket, $qty): \App\Models\Cart
    {
        $cart = $this->getCart();
        if ($cart->cartItems->count() > 0) {
            $cartItem = CartItem::where('event_ticket_id', '=', $eventTicket->id)->first();
            $cartItem->qty = $qty;
            $cartItem->row_price = $eventTicket->price * $qty;
            $cartItem->single_price = $eventTicket->price;
            $cartItem->save();
            
            return $cart;
        }
        // for now no qty.
        // $qty = self::MINIMUM_QUANTITY;
        $cartItem = new \App\Models\CartItem([
            'event_ticket_id' => $eventTicket->id,
            'qty' => $qty,
            'row_price' => $eventTicket->price * $qty,
            'single_price' => $eventTicket->price
        ]);

        $cart->cartItems()->save($cartItem);

        return $cart;
    }

    public function remove(int $cartItemId)
    {
        $cart = $this->getCart();
        $cartItem = $cart->cartItems()->where('id', '=', $cartItemId)->first();
        if ($cartItem === null) {
            throw new \Exception('Cart item requested to remove is not attached to your session cart!');
        }
        $cartItem->delete();
    }

    public function getTickets(): Collection
    {
        return Collection::make([1, 2, 3]);
    }

    private function loadDatabaseCart($cartId = 0)
    {
        $cart = \App\Models\Cart::findOrNew($cartId);
        if ($cart->id === null) {
            $cart->save();
        }

        return $cart;
    }
}
