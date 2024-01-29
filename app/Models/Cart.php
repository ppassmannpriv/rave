<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Cart
 *
 * @property int $id
 * @property bool $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CartItem[] $cartItems
 * @property-read \App\Models\User|null $user
 * @mixin \Eloquent
 */
class Cart extends Model
{
    use HasFactory;
    private const PAYPAL_PERCENTAGE = 0.0249;
    private const PAYPAL_FIXED_COST = 0.35;

    public $table = 'carts';
    public $primaryKey = 'id';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'user_id',
        'active',
        'created_at',
        'updated_at',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cartItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function getSubTotal(): float
    {
        return $this->cartItems->where('type', '!=', 'FEE')->sum('row_price');
    }

    public function getPayPalCost(): float
    {
        $subTotal = $this->getSubTotal();
        return round(($subTotal * self::PAYPAL_PERCENTAGE) + self::PAYPAL_FIXED_COST, 2);
    }

    public function getTotal(): float
    {
        return $this->getSubTotal() + $this->getPayPalCost();
    }
}
