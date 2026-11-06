<?php

/*
|--------------------------------------------------------------------------
| PayPal Friends & Family CSV report columns
|--------------------------------------------------------------------------
|
| Column headers of the PayPal activity export that the reconciliation scan
| (payment_methods:paypal_ff:scan-report) reads. The defaults are the German
| headers PayPal currently exports; override them via env if the export
| language or format changes, without touching code.
|
*/

return [
    'columns' => [
        'amount' => env('PAYPAL_FF_COLUMN_AMOUNT', 'Brutto'),
        'note' => env('PAYPAL_FF_COLUMN_NOTE', 'Hinweis'),
        'user_email' => env('PAYPAL_FF_COLUMN_USER_EMAIL', 'Absender E-Mail-Adresse'),
    ],
];
