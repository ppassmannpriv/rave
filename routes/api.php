<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Session\Middleware\StartSession;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware([
    StartSession::class,
])->group(function () {
    Route::resources([
        'cart' => CartController::class,
    ], ['except' => ['create', 'destroy']]);
    Route::put('cart/addToCart/product/{productId}/qty/{qty}', [CartController::class, 'addToCart']);
    Route::resources([
        'product' => ProductController::class,
    ], ['except' => ['create', 'destroy']]);
});
