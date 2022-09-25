<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    public const STATE_INIT = 'initialized';
    public const STATE_ORDERED = 'ordered';
    public const STATE_PAID = 'paid';
    public const STATE_SUCCESS = 'successful';
    public const STATE_CANCEL = 'cancel';

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
}
