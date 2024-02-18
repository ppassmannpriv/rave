<?php

namespace App\Models;

use App\Models\Transaction\PartialTransaction;
use Illuminate\Database\Eloquent\Model;
use App\Events\TransactionSavedEvent;

/**
 * App\Models\Transaction
 *
 * @property int $id
 * @property int|null $payment_method_id
 * @property int|null $order_id
 * @property string $reference
 * @property string $state
 * @property string|null $token
 * @property string|null $payer_id
 * @property string|null $payer_email
 * @property float $amount
 * @property string|null $comment
 * @property-read \App\Models\PaymentMethod|null $paymentMethod
 * @property-read \App\Models\Order|null $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PartialTransaction> $partialTransactions
 * @property-read int|null $partial_transactions_count
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction wherePayerEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction wherePayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction wherePaymentMethodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Transaction extends Model
{
    public const STATE_INIT = 'initialized';
    public const STATE_ORDERED = 'ordered';
    public const STATE_PAID = 'paid';
    public const STATE_SUCCESS = 'successful';
    public const STATE_CANCEL = 'cancel';
    public const STATE_PROCESSING = 'processing';
    public const REFERENCE_LENGTH = 10;

    public $table = 'transactions';
    public $primaryKey = 'id';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'payment_method_id',
        'order_id',
        'reference',
        'amount',
        'state',
        'token',
        'payer_id',
        'payer_email',
        'comment',
        'created_at',
        'updated_at',
    ];

    protected $dispatchesEvents = [
        'saved' => TransactionSavedEvent::class,
    ];

    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function paymentMethod(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id', 'id');
    }

    public function partialTransactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PartialTransaction::class, 'transaction_id', 'id');
    }
}
