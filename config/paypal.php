<?php

return [
    'paypal_username' => env('PAYPAL_USERNAME', 'example@example.com'),
    'paypal_password' => env('PAYPAL_PASSWORD', 'example'),
    'paypal_seller_account_id' => env('PAYPAL_SELLER_ACCOUNT_ID', '1234'),
    'paypal_brand_name' => env('PAYPAL_BRAND_NAME'),
    'paypal_signature' => env('PAYPAL_SIGNATURE'),
    'paypal_is_test' => env('PAYPAL_IS_TEST'),
    'currency' => env('CURRENCY', 'EUR'),
    'currency_symbol' => env('CURRENCY_SYMBOL', '€'),
];