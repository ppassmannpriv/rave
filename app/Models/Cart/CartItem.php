<?php

namespace App\Models\Cart;

use Database\Factories\Cart\CartItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    /** @use HasFactory<CartItemFactory> */
    use HasFactory;
}
