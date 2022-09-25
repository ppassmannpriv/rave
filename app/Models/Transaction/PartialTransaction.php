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
