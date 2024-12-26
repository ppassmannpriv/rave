<?php

namespace App\Http\Controllers;

use App\Actions\Cart\AddToCart;
use App\Actions\Cart\RemoveFromCart;
use App\Cart\CartManager;
use App\Http\Requests\Cart\StoreCartItemRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Http\Requests\StoreCartRequest;
use App\Http\Requests\UpdateCartRequest;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Request;

class CartController extends Controller
{
    public function __construct(private CartManager $cartManager)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $cart = $this->cartManager->getOrCreateCart();

        return response($cart);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCartRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Cart $cart)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cart $cart)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCartRequest $request, Cart $cart)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cart $cart)
    {
        //
    }

    public function addToCart(StoreCartItemRequest $request)
    {
        $cart = $this->cartManager->getOrCreateCart();
        $product = Product::findOrFail($request->get('product_id'));
        AddToCart::run($cart, $product, $request->get('qty'));

        return response($cart);
    }

    public function removeFromCart(UpdateCartItemRequest $request)
    {
        $cart = $this->cartManager->getOrCreateCart();
        $product = Product::findOrFail($request->get('product_id'));
        RemoveFromCart::run($cart, $product);

        return response($cart);
    }
}
