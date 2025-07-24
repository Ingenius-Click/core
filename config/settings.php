<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Settings Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the cache settings for your application settings.
    | By default, settings are cached to improve performance.
    |
    */
    'cache' => [
        'enabled' => env('SETTINGS_CACHE_ENABLED', true),
        'prefix' => 'settings_',
        'ttl' => 86400, // 24 hours
    ],

    /*
    |--------------------------------------------------------------------------
    | Settings Encryption Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure whether sensitive settings should be encrypted.
    | By default, encryption is enabled for security.
    |
    */
    'encryption' => [
        'enabled' => env('SETTINGS_ENCRYPTION_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Settings Groups
    |--------------------------------------------------------------------------
    |
    | Here you may define default settings groups for your application.
    | These will be available across your application.
    |
    */
    'groups' => [
        'general',
        'mail',
        'invoices',
        'orders',
        'payforms',
    ],

    /*
    |--------------------------------------------------------------------------
    | Settings Classes
    |--------------------------------------------------------------------------
    |
    | Here you may register settings classes that can be used to define
    | strongly typed settings objects.
    |
    */
    'settings_classes' => [
        // Settings classes will be registered by individual packages
    ],
];
