<?php declare(strict_types=1);

namespace App\Cart;

use App\Models\Cart;
use App\Actions\Cart\AddToCart;
use App\Actions\Cart\RemoveFromCart;
use App\Models\Product;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Auth;

class CartManager
{
    private const int MINIMUM_QUANTITY = 1;
    private ?Cart $cart = null;

    public function __construct(private readonly SessionManager $session)
    {
        $this->cart = $this->getOrCreateCart();
    }

    public function getOrCreateCart(): Cart
    {
        // @TODO: Needs auth somehow - this cannot stay this way!
        $this->session->save();
        $sessionId = $this->session->getId();

        /**
         * @var Cart $cart
         */
        $cart = Cart::with('cartItems')->firstOrCreate(['session_id' => $sessionId]);
        $cart->active = true;
        $cart->update(['active']);

        return $cart;
    }

    public function add(Product $product, int $quantity = self::MINIMUM_QUANTITY): Cart
    {
        AddToCart::run($this->cart, $product, $quantity);

        return $this->cart;
    }

    public function remove(Product $product, int $quantity = self::MINIMUM_QUANTITY): Cart
    {
        RemoveFromCart::run($this->cart, $product, $quantity);
        return $this->cart;
    }
}
