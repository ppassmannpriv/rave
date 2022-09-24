<?php

namespace App\Services;

use App\Contracts\Cart;
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
        if ($this->session->get(self::DEFAULT_INSTANCE) === null) {
            $cart = $this->loadDatabaseCart();
            $cart->save();
            $this->session->put(self::DEFAULT_INSTANCE, $cart->id);
            return $cart;
        }
        return $this->loadDatabaseCart($this->session->get(self::DEFAULT_INSTANCE));
    }

    public function add($payload)
    {
        $cart = $this->getCart();
        dd($cart);
    }

    public function remove($payload)
    {
        $cart = $this->getCart();
        dd($cart);
//        $content = $this->getContent();
//
//        if (!$content->has($payload->id)) {
//            throw new \Exception('Cart item could not be found!');
//        }
//        $content->forget($payload->id);
//
//        $this->session->put(self::DEFAULT_INSTANCE, $content);
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
