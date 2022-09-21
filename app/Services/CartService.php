<?php

namespace App\Services;

use App\Contracts\Cart;
use Illuminate\Support\Collection;
use Illuminate\Session\SessionManager;

class CartService implements Cart {
    const MINIMUM_QUANTITY = 1;
    const TTL = 900;
    const DEFAULT_INSTANCE = 'cart';

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

    /**
     * Returns the content of the cart.
     *
     * @return Collection
     */
    public function content(): Collection
    {
        return is_null($this->session->get(self::DEFAULT_INSTANCE)) ? collect([]) : $this->session->get(self::DEFAULT_INSTANCE);
    }

    /**
     * Returns the content of the cart.
     *
     * @return Collection
     */
    protected function getContent(): Collection
    {
        return $this->session->has(self::DEFAULT_INSTANCE) ? $this->session->get(self::DEFAULT_INSTANCE) : collect([]);
    }

    public function add($payload)
    {
        $content = $this->getContent();

        if ($content->has($payload['id'])) {
            $payload['id'] = $payload['id'] + $content->count();
        }

        $content->put($payload['id'], $payload['content']);

        $this->session->put(self::DEFAULT_INSTANCE, $content);
    }

    public function getTickets(): Collection
    {
        return Collection::make([1, 2, 3]);
    }
}
