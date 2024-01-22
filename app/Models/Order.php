<?php

namespace App\Models;

use \DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Order\OrderItem;

/**
 * App\Models\Order
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string $status
 * @property int|null $user_id
 * @property int|null $transaction_id
 * @property-read \App\Models\Transaction|null $transaction
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Order\OrderItem[] $orderItems
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order newQuery()
 * @method static \Illuminate\Database\Query\Builder|Order onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereEventTicketCodeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|Order withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Order withoutTrashed()
 * @mixin \Eloquent
 */
class Order extends Model
{
    use SoftDeletes;
    use HasFactory;

    public const STATUS_INITIALIZED = 'initialized';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_PAID = 'paid';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_CANCELLED = 'cancelled';

    public $table = 'orders';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'user_id',
        'status',
        'price',
        'transaction_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transaction(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

    public function quantityItems(): int
    {
        $qty = 0;
        foreach($this->orderItems as $orderItem) {
            $qty += $orderItem->qty;
        }

        return $qty;
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function isCancellable()
    {
        return !in_array($this->status, [
            static::STATUS_CLOSED,
            static::STATUS_CANCELLED,
            static::STATUS_PAID
        ]);
    }

    public function toPayPalExpressArray(): array
    {
        $items = [];
        foreach ($this->orderItems as $orderItem) {
            $items[] = [
                'name' => $orderItem->eventTicket->getType(),
                'price' => $orderItem->row_price,
                'description' => $orderItem->eventTicket->event->name,
                'quantity' => $orderItem->qty,
            ];
        }
        return [
            'amount' => $this->price,
            'currency' => config('paypal.currency'),
            'returnUrl' => 'https://schleuse.eu/payment/return',
            'cancelUrl' => 'https://schleuse.eu/payment/cancel',
            'transacionId' => $this->transaction->id,
        ];
    }
}
