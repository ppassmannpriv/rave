<?php

namespace App\Models;

use App\Models\Order\OrderItem;
use \DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\EventTicketCode
 *
 * @property int $id
 * @property string $code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int $event_ticket_id
 * @property int|null $order_item_id
 * @property-read \App\Models\Order\OrderItem|null $orderItem
 * @property-read \App\Models\EventTicket|null $eventTicket
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicketCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicketCode newQuery()
 * @method static \Illuminate\Database\Query\Builder|EventTicketCode onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicketCode query()
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicketCode whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicketCode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicketCode whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicketCode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicketCode whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|EventTicketCode withTrashed()
 * @method static \Illuminate\Database\Query\Builder|EventTicketCode withoutTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicketCode whereEventTicketId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicketCode whereOrderItemId($value)
 * @mixin \Eloquent
 */
class EventTicketCode extends Model
{
    use SoftDeletes;
    use HasFactory;

    public $table = 'event_ticket_codes';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'code',
        'event_ticket_id',
        'order_item_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function eventTicket(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(EventTicket::class, 'event_ticket_id');
    }

    public function orderItem(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function delete()
    {
        if ($this->orderItem === null) {
            throw new \Exception('You cannot delete a code that has been purchased.');
        }
        return parent::delete();
    }

    public function isSold()
    {
        return in_array($this->orderItem?->order?->status, [
            Order::STATUS_PAID,
            Order::STATUS_CLOSED
        ]);
    }
}
