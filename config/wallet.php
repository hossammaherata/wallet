<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Wallet System Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Wallet System integration.
    |
    */

    'api_url' => env('WALLET_API_URL', 'http://46.62.241.66'),
    'api_key' => env('WALLET_API_KEY', 'mdn-wallet-9088db076fcd7f0fad256821f8a3c3c9cba4cdcab915559e7c894d5c4e7bc564'),
    'webhook_enabled' => env('WALLET_WEBHOOK_ENABLED', true),
];


