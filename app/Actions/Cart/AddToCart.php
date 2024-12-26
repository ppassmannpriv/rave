<?php

namespace App\Actions\Cart;

use App\Exceptions\Cart\SoldOutException;
use App\Models\Cart;
use App\Models\Product;
use App\Services\CartService;
use Lorisleiva\Actions\Concerns\AsAction;
use \App;


class AddToCart
{
    use AsAction;

    public function handle(Cart $cart, Product $product, int $qty = 1): void
    {
        if ($product->isAvailable() === false) {
            throw new SoldOutException('Item is sold out.');
        }
        $cartItem = $cart->cartItems()->where('product_id', $product->id)->firstOrNew([
            'product_id' => $product->id,
            'cart_id' => $cart->id,
            'single_price' => $product->price,
            'qty' => $qty,
            'total_price' => $product->price * $qty,
            'type' => $product->type,
        ]);
        $cartItem->save();
    }
}
