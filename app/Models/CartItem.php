<?php

namespace App\Models;

use Carbon\Carbon;
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
class CartItem extends Model
{
    use HasFactory;

    public $table = 'cart_items';
    public $primaryKey = 'id';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'event_ticket_id',
        'cart_id',
        'qty',
        'single_price',
        'row_price',
        'created_at',
        'updated_at',
    ];

    public function eventTicket()
    {
        return $this->belongsTo(EventTicket::class, 'event_ticket_id');
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id');
    }
}
