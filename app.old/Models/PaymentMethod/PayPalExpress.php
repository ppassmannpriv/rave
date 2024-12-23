<?php

namespace App\Models\PaymentMethod;

use App\Actions\Payment\PayPalExpress\InitTransaction;
use App\Models\PaymentMethod;
use App\Models\Transaction;

/**
 * App\Models\PaymentMethod\PayPalExpress
 *
 * @property int $id
 * @property string $alias
 * @property string $name
 * @property string $FQN
 * @property string|null $description
 * @property int $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PayPalExpress newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PayPalExpress newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PayPalExpress query()
 * @method static \Illuminate\Database\Eloquent\Builder|PayPalExpress whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayPalExpress whereAlias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayPalExpress whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayPalExpress whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayPalExpress whereFQN($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayPalExpress whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayPalExpress whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayPalExpress whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PayPalExpress extends PaymentMethod
{
    public const ALIAS = 'paypal_express';

    public function handle(Transaction $transaction)
    {
        return InitTransaction::make()->handle($transaction);
    }
}
