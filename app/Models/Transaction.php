<?php

namespace App\Models;

use App\Models\Transaction\PartialTransaction;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Transaction
 *
 * @property int $id
 * @property int|null $payment_method_id
 * @property int|null $order_id
 * @property string $reference
 * @property string $state
 * @property float $amount
 * @property-read \App\Models\PaymentMethod|null $paymentMethod
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @mixin \Eloquent
 */
class Transaction extends Model
{
    public const STATE_INIT = 'initialized';
    public const STATE_ORDERED = 'ordered';
    public const STATE_PAID = 'paid';
    public const STATE_SUCCESS = 'successful';
    public const STATE_CANCEL = 'cancel';
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
        'created_at',
        'updated_at',
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
