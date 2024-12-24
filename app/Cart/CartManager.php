<?php declare(strict_types=1);

namespace App\Cart;

use App\Models\Cart;
use App\Actions\Cart\AddToCart;
use App\Models\Product;
use Illuminate\Session\SessionManager;

class CartManager
{
    const MINIMUM_QUANTITY = 1;
    private ?Cart $cart = null;

    public function __construct(private SessionManager $session)
    {}

    public function getOrCreateCart(): Cart
    {
        if ($this->cart === null) {
            $this->cart = new Cart();
        }
        return $this->cart;
    }

    public function add(Product $product, int $quantity = 1): Cart
    {
        $cart = $this->getOrCreateCart();
        AddToCart::class;

        return $cart;
    }
}
