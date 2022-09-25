<?php

namespace App\Models\Order;

use App\Models\CartItem;
use App\Models\EventTicket;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function cartItem()
    {
        return $this->hasOne(CartItem::class, 'cart_item_id');
    }

    public function eventTicket()
    {
        return $this->hasOne(EventTicket::class, 'event_ticket_id');
    }
}
