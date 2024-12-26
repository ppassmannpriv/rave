<?php

namespace App\Actions\Cart;

use App\Exceptions\Cart\SoldOutException;
use App\Models\Cart;
use App\Models\Product;
use Lorisleiva\Actions\Concerns\AsAction;

class RemoveFromCart
{
    use AsAction;

    public function handle(Cart $cart, Product $product, int $qty = 1): void
    {
        // @TODO: This needs validation to not delete fees automatically!
        $cart->cartItems()->where('product_id', $product->id)->delete();
    }
}
