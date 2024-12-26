<?php

namespace App\Actions\Cart;

use App\Exceptions\Cart\SoldOutException;
use App\Models\Cart;
use App\Models\Product;
use Lorisleiva\Actions\Concerns\AsAction;

class RemoveFromCart
{
    use AsAction;

    public function handle(Cart $cart, Product $product): void
    {
        $cartItem = $cart->cartItems()->where('product_id', $product->id)->delete();
        $cart->refresh();
    }
}
