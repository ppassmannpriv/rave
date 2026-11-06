<?php

return [
    'mode' => env('PAYPAL_BUSINESS_MODE', 'sandbox'),
    'client_id' => env('PAYPAL_BUSINESS_CLIENT_ID'),
    'client_secret' => env('PAYPAL_BUSINESS_CLIENT_SECRET'),
    'webhook_id' => env('PAYPAL_BUSINESS_WEBHOOK_ID'),
    'currency' => env('CURRENCY') ?: 'EUR',
    'base_urls' => [
        'sandbox' => 'https://api-m.sandbox.paypal.com',
        'live' => 'https://api-m.paypal.com',
    ],
];
