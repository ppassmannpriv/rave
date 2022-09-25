<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CartItem
 *
 * @property int $id
 * @property int $qty
 * @property float $single_price
 * @property float $row_price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\EventTicket|null $eventTicket
 * @mixin \Eloquent
 */
class PaymentMethod extends Model
{
    use HasFactory;

    public const ALIAS = null;

    public $table = 'payment_methods';
    public $primaryKey = 'id';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'name',
        'alias',
        'active',
        'FQN',
        'created_at',
        'updated_at',
    ];
}
