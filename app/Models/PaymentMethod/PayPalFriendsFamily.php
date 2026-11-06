<?php

namespace App\Models\PaymentMethod;

use App\Models\PaymentMethod;

class PayPalFriendsFamily extends PaymentMethod
{
    public const ALIAS = 'paypal_ff';
    public const REPORT_DIR = 'paymentMethods/paypal_ff';
    public const REPORT_FILE_NAME = 'transaction_records';

    public static function amountColumn(): string
    {
        return config('paypal_ff.columns.amount');
    }

    public static function noteColumn(): string
    {
        return config('paypal_ff.columns.note');
    }

    public static function userEmailColumn(): string
    {
        return config('paypal_ff.columns.user_email');
    }

    /**
     * Headers every export must contain for reconciliation to work at all.
     *
     * @return string[]
     */
    public static function requiredColumns(): array
    {
        return [self::amountColumn(), self::noteColumn()];
    }
}
