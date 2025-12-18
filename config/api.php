<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Authentication
    |--------------------------------------------------------------------------
    |
    | These credentials are used to authenticate external API requests
    | to the CRM endpoints. Make sure to keep these secure and change
    | them from the default values in production.
    |
    */

    'auth_token' => env('API_AUTH_TOKEN', 'your-secure-token-here'),
    'auth_secret' => env('API_AUTH_SECRET', 'your-secure-secret-here'),

    /*
    |--------------------------------------------------------------------------
    | API Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for API endpoints
    |
    */

    'rate_limit' => [
        'max_attempts' => env('API_RATE_LIMIT', 100),
        'decay_minutes' => env('API_RATE_LIMIT_DECAY', 1),
    ],
];
