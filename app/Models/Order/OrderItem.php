<?php

namespace App\Models\Order;

use App\Models\CartItem;
use App\Models\EventTicket;
use App\Models\EventTicketCode;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Order
 *
 * @property int $id
 * @property int $qty
 * @property int|null $order_id
 * @property int|null $cart_item_id
 * @property int|null $event_ticket_id
 * @property float $single_price
 * @property float $row_price
 * @property-read \App\Models\EventTicket|null $eventTicket
 * @property-read \App\Models\Order|null $order
 * @property-read \App\Models\CartItem|null $cartItem
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\EventTicketCode[]|null $eventTicketCodes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class OrderItem extends Model
{
    use SoftDeletes;
    use HasFactory;

    public $table = 'order_items';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'order_id',
        'qty',
        'single_price',
        'row_price',
        'cart_item_id',
        'event_ticket_id',
        'type',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function cartItem()
    {
        return $this->hasOne(CartItem::class, 'id', 'cart_item_id');
    }

    public function eventTicket()
    {
        return $this->hasOne(EventTicket::class, 'id', 'event_ticket_id');
    }

    public function eventTicketCodes()
    {
        return $this->hasMany(EventTicketCode::class, 'order_item_id', 'id');
    }
}
