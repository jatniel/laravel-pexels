<?php

// config for Jatniel/Pexels
return [
    /*
    |--------------------------------------------------------------------------
    | Pexels API Key
    |--------------------------------------------------------------------------
    |
    | Your Pexels API key. Get one at: https://www.pexels.com/api/
    | In production, uses PEXELS_API_KEY. If PEXELS_API_KEY_TEST exists
    | and APP_ENV is not production, it will use the test key.
    |
    */
    'api_key' => env('PEXELS_API_KEY'),
    'api_key_test' => env('PEXELS_API_KEY_TEST'),

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => env('PEXELS_CACHE_ENABLED', true),
        'ttl' => env('PEXELS_CACHE_TTL', 3600), // 1 hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for downloading photos locally.
    |
    */
    'storage' => [
        'disk' => env('PEXELS_STORAGE_DISK', 'public'),
        'path' => env('PEXELS_STORAGE_PATH', 'pexels'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Pexels free plan: 200 requests/hour
    | Pexels paid plan: configure accordingly
    |
    */
    'rate_limit' => [
        'enabled' => env('PEXELS_RATE_LIMIT_ENABLED', true),
        'request_per_hour' => env('PEXELS_RATE_LIMIT_REQUEST', 200),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Settings
    |--------------------------------------------------------------------------
    |
    | For async photo downloads.
    |
    */
    'queue' => [
        'enabled' => env('PEXELS_QUEUE_ENABLED', true),
        'connection' => env('PEXELS_QUEUE_CONNECTION'),
        'name' => env('PEXELS_QUEUE_NAME', 'pexels'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Attribution Settings
    |--------------------------------------------------------------------------
    |
    | Pexels requires attribution to photographers.
    | https://www.pexels.com/license/
    |
    */
    'attribution' => [
        'format' => 'Photo by :photographer on Pexels',
        'link_to_profile' => true,
    ],
];
