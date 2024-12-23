<?php

namespace App\Models\PaymentMethod;

use App\Models\PaymentMethod;
use App\Models\Transaction;

/**
 * App\Models\PaymentMethod\PayPalFriendsFamily
 *
 * @property int $id
 * @property string $alias
 * @property string $name
 * @property string $FQN
 * @property string|null $description
 * @property int $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PayPalFriendsFamily newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PayPalFriendsFamily newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PayPalFriendsFamily query()
 * @method static \Illuminate\Database\Eloquent\Builder|PayPalFriendsFamily whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayPalFriendsFamily whereAlias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayPalFriendsFamily whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayPalFriendsFamily whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayPalFriendsFamily whereFQN($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayPalFriendsFamily whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayPalFriendsFamily whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayPalFriendsFamily whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PayPalFriendsFamily extends PaymentMethod
{
    public const ALIAS = 'paypal_ff';
    public const REPORT_DIR = 'paymentMethods/paypal_ff';
    public const REPORT_FILE_NAME = 'transaction_records';
    public const NOTE_COLUMN = 'Hinweis';
    public const USER_EMAIL_COLUMN = 'Absender E-Mail-Adresse';
    public const AMOUNT_COLUMN = 'Brutto';

    public function handle(Transaction $transaction)
    {
        return \Redirect::to('/cart/success');
    }
}
