<?php

namespace App\Models\PaymentMethod;

use App\Actions\Payment\PayPalExpress\InitTransaction;
use App\Models\PaymentMethod;
use App\Models\Transaction;

class PayPalExpress extends PaymentMethod
{
    public const ALIAS = 'paypal_express';

    public function handle(Transaction $transaction)
    {
        return InitTransaction::make()->handle($transaction);
    }
}
