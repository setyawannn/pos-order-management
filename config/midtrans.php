<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may specify the configuration for Midtrans payment gateway.
    | This includes merchant ID, server key, client key, and whether to
    | use production or sandbox environment.
    |
    */

    'merchant_id' => env('MIDTRANS_MERCHANT_ID', ''),
    'server_key' => env('MIDTRANS_SERVER_KEY', ''),
    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'snap_url' => env('MIDTRANS_SNAP_URL', 'https://app.sandbox.midtrans.com/snap/v1/transactions'),

];
