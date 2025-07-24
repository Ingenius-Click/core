# IngeniusCore

A Laravel core package with shared resources and tenancy features for Ingenius Click applications.

## Features

- Multi-tenancy support with Stancl Tenancy
- Tenant-aware session handling
- Permissions management
- Media library integration
- Shared interfaces, constants, and helpers
- Tenant commands for migrations and module support

## Installation

### 1. Add the package to your composer.json

```json
"require": {
    "ingenius/core": "*"
}
```

```json
"repositories": [
    {
        "type": "path",
        "url": "packages/ingenius/core"
    }
]
```

### 2. Install the package

```bash
composer require ingenius/core
```

### 3. Run the installation command

The easiest way to install the package is to run the installation command:

```bash
php artisan ingenius:install
```

This command will:
- Publish configuration files
- Publish migrations
- Update your bootstrap/app.php file with the required middleware configuration
- Update your auth.php file with tenant guard and provider configuration
- Run migrations (optional)

### 4. Manual installation (alternative)

If you prefer to install manually, follow these steps:

#### 4.1 Publish the configuration files

```bash
php artisan vendor:publish --provider="Ingenius\Core\Providers\CoreServiceProvider" --tag="ingenius-core-config"
```

#### 4.2 Publish the migrations

```bash
php artisan vendor:publish --provider="Ingenius\Core\Providers\CoreServiceProvider" --tag="ingenius-core-migrations"
```

#### 4.3 Update your bootstrap/app.php file

Make sure your bootstrap/app.php file includes the following middleware configuration:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->api(prepend: [
        Illuminate\Session\Middleware\StartSession::class,
        Illuminate\Cookie\Middleware\EncryptCookies::class,
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    ]);
    $middleware->statefulApi();
    $middleware->group('universal', []);
})
```

This configuration is required for proper functioning of the multi-tenancy and authentication features.

#### 4.4 Update your auth.php file

Make sure your auth.php file includes the following tenant configuration:

```php
// In the 'guards' array:
'tenant' => [
    'driver' => 'session',
    'provider' => 'tenant_users',
],

// In the 'providers' array:
'tenant_users' => [
    'driver' => 'eloquent',
    'model' => \Ingenius\Auth\Models\User::class,
],

// In the 'passwords' array:
'tenant_users' => [
    'provider' => 'tenant_users',
    'table' => 'password_reset_tokens',
    'expire' => 60,
    'throttle' => 60,
],
```

This configuration is required for tenant authentication to work properly.

#### 4.5 Run the migrations

```bash
php artisan migrate
```

## Usage

### Tenancy

The package provides a complete tenancy solution based on Stancl Tenancy. You can create and manage tenants with the following:

```php
use Ingenius\Core\Models\Tenant;

// Create a new tenant
$tenant = Tenant::create(['id' => 'tenant-id']);
$tenant->domains()->create(['domain' => 'tenant.example.com']);

// Initialize tenancy
tenancy()->initialize($tenant);

// End tenancy
tenancy()->end();
```

### Permissions

The package provides a permissions manager for registering and managing permissions:

```php
use Ingenius\Core\Support\PermissionsManager;

$permissionsManager = app(PermissionsManager::class);

// Register permissions
$permissionsManager->registerMany([
    'users.view' => 'View users',
    'users.create' => 'Create users',
], 'Users', 'tenant');

// Get all permissions
$permissions = $permissionsManager->all();

// Get tenant permissions
$tenantPermissions = $permissionsManager->tenant();

// Get central permissions
$centralPermissions = $permissionsManager->central();
```

### Commands

The package provides several useful commands:

```bash
# Add tenant support to a module
php artisan module:tenant-support ModuleName

# Run migrations for all tenants in all modules
php artisan tenants:migrate-modules

# Run migrations for a specific tenant
php artisan tenants:migrate-modules --tenant=tenant-id

# Run migrations for a specific module
php artisan tenants:migrate-modules --module=ModuleName

# Rollback migrations for all tenants in all modules
php artisan tenants:rollback-modules
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information. 