<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Core Package Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure the core package settings including models,
    | authentication, and other core functionality.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Central User Model
    |--------------------------------------------------------------------------
    |
    | This option controls which User model to use for central application
    | authentication. By default, it will use the Laravel default App\Models\User
    | if it exists, otherwise it will fall back to the core package User model.
    | You can override this by setting the CENTRAL_USER_MODEL environment variable.
    |
    */
    'central_user_model' => env('CENTRAL_USER_MODEL', class_exists('App\\Models\\User') ? 'App\\Models\\User' : 'Ingenius\\Core\\Models\\User'),

    /*
    |--------------------------------------------------------------------------
    | User Model Publishing
    |--------------------------------------------------------------------------
    |
    | These settings control whether the core User model and migration should
    | be automatically published during installation.
    |
    */
    'publish_user_model' => env('CORE_PUBLISH_USER_MODEL', false),
    'publish_user_migration' => env('CORE_PUBLISH_USER_MIGRATION', false),

    /*
    |--------------------------------------------------------------------------
    | Central Authentication Guard
    |--------------------------------------------------------------------------
    |
    | The guard name to use for central application authentication.
    | This is separate from tenant authentication.
    |
    */
    'central_auth_guard' => env('CENTRAL_AUTH_GUARD', 'web'),

    /*
    |--------------------------------------------------------------------------
    | Tenant User Model
    |--------------------------------------------------------------------------
    |
    | This option controls which User model to use for tenant application
    | authentication. By default, it will use the auth package User model.
    | You can override this by setting the TENANT_USER_MODEL environment variable.
    |
    */
    'tenant_user_model' => env('TENANT_USER_MODEL', 'Ingenius\\Auth\\Models\\User'),

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | Feature flags for core package functionality.
    |
    */
    'features' => [
        'central_auth' => env('CORE_CENTRAL_AUTH_ENABLED', true),
        'user_management' => env('CORE_USER_MANAGEMENT_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Table Handler
    |--------------------------------------------------------------------------
    |
    | This option controls which table handler implementation to use when
    | the AbstractTableHandler is resolved from the service container.
    | The configured class must extend AbstractTableHandler.
    |
    */
    'table_handler' => env('CORE_TABLE_HANDLER', 'Ingenius\\Core\\Services\\GenericTableHandler'),
];
