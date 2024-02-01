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
 * @property float $price
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
            if ($orderItem->cartItem?->type === 'FEE') {
                continue;
            }
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

        /**
         * @var ?Cart $cart
         */
        $cart = null;
        foreach ($this->orderItems as $orderItem) {
            if ($cart === null) {
                $cart = $orderItem->cartItem->cart;
            }

            $name = null;
            $description = null;
            switch ($orderItem->cartItem) {
                case 'FEE':
                    $name = 'Transaction fee';
                    $description = 'Transaction fees for ticketing, e-mail and PayPal.';
                    break;
                case 'TICKET':
                    $name = $orderItem->eventTicket->getType();
                    $description = $orderItem->eventTicket->event->name;
                    break;
                case 'VIRTUAL':
                    $name = 'Virtual goods';
                    $description = 'Non-physical assets.';
                    break;
                case 'SHIPPING':
                    $name = 'Shipping cost';
                    $description = 'Packaging, handling and posting of your orders.';
                    break;
            }

            $items[] = [
                'name' => $name,
                'price' => $orderItem->single_price,
                'description' => $description,
                'quantity' => $orderItem->qty,
            ];
        }

        return [
            'amount' => $this->price,
            'currency' => config('paypal.currency'),
            'items' => $items,
            'returnUrl' => url('payment/return'),
            'cancelUrl' => url('payment/cancel'),
            'transacionId' => $this->transaction->reference,
            'noShipping' => 1,
        ];
    }
}
