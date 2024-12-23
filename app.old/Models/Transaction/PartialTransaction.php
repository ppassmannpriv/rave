<?php

namespace App\Models\Transaction;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Transaction\PartialTransaction
 *
 * @property int $id
 * @property int|null $transaction_id
 * @property float $amount
 * @property string $raw
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Transaction|null $transaction
 * @method static \Illuminate\Database\Eloquent\Builder|PartialTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PartialTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PartialTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|PartialTransaction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PartialTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PartialTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PartialTransaction whereRaw($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PartialTransaction whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PartialTransaction whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PartialTransaction extends Model
{
    public $table = 'transaction_partials';
    public $primaryKey = 'id';

    protected $casts = [
        'raw' => 'array'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'transaction_id',
        'amount',
        'raw',
        'created_at',
        'updated_at',
    ];

    public function transaction(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'id');
    }
}
