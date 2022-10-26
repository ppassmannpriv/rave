<?php

namespace App\Models\PaymentMethod;

use App\Models\PaymentMethod;

class PayPalFriendsFamily extends PaymentMethod
{
    public const ALIAS = 'paypal_ff';
    public const REPORT_DIR = 'paymentMethods/paypal_ff';
    public const REPORT_FILE_NAME = 'transaction_records';
    public const NOTE_COLUMN = 'Hinweis';
    public const USER_EMAIL_COLUMN = 'Absender E-Mail-Adresse';
    public const AMOUNT_COLUMN = 'Brutto';
}
