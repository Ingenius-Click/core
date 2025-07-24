# IngeniusCore

A comprehensive Laravel core package with shared resources, multi-tenancy, and modular package management for Ingenius Click applications.

## Features

- **Multi-tenancy support** with Stancl Tenancy
- **Template and Feature Management** for tenant customization
- **Settings Management** with encryption, caching, and group organization
- **Sequence Generator** for automatic number generation (invoices, orders, etc.)
- **Tenant Initialization System** with package-specific initializers
- **Package Creation Tools** for modular development
- **Permissions Management** for both central and tenant contexts
- **Configuration and Migration Registry** for centralized management
- **Media Library Integration** with Spatie Media Library
- **Enhanced Console Commands** for package and tenant management
- **Comprehensive Helper Functions** for common operations
- **HTTP Controllers and API Endpoints** for settings and template management

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
- Setup central User model
- Install basic packages (optional)
- Create basic templates with default features
- Run migrations (optional)
- Create a basic admin user (optional)

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

The package provides a complete tenancy solution based on Stancl Tenancy. You can create and manage tenants with templates and features:

```php
use Ingenius\Core\Models\Tenant;
use Ingenius\Core\Models\Template;

// Create a new tenant with a template
$template = Template::where('identifier', 'basic')->first();
$tenant = Tenant::create([
    'id' => 'tenant-id',
    'template_id' => $template->id,
]);
$tenant->setName('My Tenant');
$tenant->domains()->create(['domain' => 'tenant.example.com']);

// Check if tenant has specific features
if ($tenant->hasFeature('feature-name')) {
    // Feature is available
}

// Initialize tenancy
tenancy()->initialize($tenant);

// End tenancy
tenancy()->end();
```

### Settings Management

The package provides a powerful settings management system with encryption, caching, and group organization:

```php
use Ingenius\Core\Services\SettingsService;
use Ingenius\Core\Facades\Settings;

// Using the facade (recommended)
Settings::set('general', 'site_name', 'My Website');
$siteName = Settings::get('general', 'site_name', 'Default Name');

// Using the service directly
$settingsService = app(SettingsService::class);
$settingsService->set('mail', 'smtp_host', 'smtp.example.com', true); // encrypted
$smtpHost = $settingsService->get('mail', 'smtp_host');

// Get all settings in a group
$generalSettings = Settings::getAllInGroup('general');

// Lock/unlock settings
Settings::lock('general', 'site_name');
Settings::unlock('general', 'site_name');

// Using the helper function
$value = settings('general', 'site_name', 'default');
$allGeneral = settings('general');
$settingsInstance = settings();
```

#### Settings Classes

You can create strongly-typed settings classes:

```php
use Ingenius\Core\Settings\Settings;

class GeneralSettings extends Settings
{
    public static function group(): string
    {
        return 'general';
    }

    public string $site_name = 'Default Site';
    public string $site_description = '';
    public bool $maintenance_mode = false;

    public static function encrypted(): array
    {
        return ['api_key'];
    }
}

// Usage
$settings = new GeneralSettings();
$settings->load(); // Load from database
$settings->site_name = 'New Site Name';
$settings->save(); // Save to database
```

### Sequence Generator

Generate sequential numbers for invoices, orders, or any other entities:

```php
use Ingenius\Core\Services\SequenceGeneratorService;

$sequenceService = app(SequenceGeneratorService::class);

// Generate next invoice number
$invoiceNumber = $sequenceService->generateNumber('invoice'); // INV-1000

// Create custom sequence
$sequenceService->createSequence(
    'order',
    'ORD-',     // prefix
    '-2024',    // suffix
    1000,       // start number
    false       // random component
);

$orderNumber = $sequenceService->generateNumber('order'); // ORD-1000-2024
```

Configure sequences in `config/sequences.php`:

```php
return [
    'invoice' => [
        'prefix' => 'INV-',
        'suffix' => null,
        'start_number' => 1000,
        'random' => false,
    ],
    'order' => [
        'prefix' => 'ORD-',
        'suffix' => null,
        'start_number' => 1000,
        'random' => false,
    ],
];
```

### Template and Feature Management

Manage tenant templates and features:

```php
use Ingenius\Core\Models\Template;
use Ingenius\Core\Services\FeatureManager;

// Create a template
$template = Template::create([
    'name' => 'E-commerce Template',
    'description' => 'Full e-commerce functionality',
    'identifier' => 'ecommerce',
    'features' => ['orders', 'products', 'payments'],
    'active' => true,
]);

// Feature management
$featureManager = app(FeatureManager::class);
$basicFeatures = $featureManager->getBasicFeatures();
$allFeatures = $featureManager->getFeatures();
```

### Tenant Initialization

Initialize tenants with package-specific data:

```php
use Ingenius\Core\Support\TenantInitializationManager;
use Ingenius\Core\Interfaces\TenantInitializer;

// Create a custom initializer
class MyPackageInitializer implements TenantInitializer
{
    public function initialize(Tenant $tenant, Command $command): void
    {
        // Initialize tenant-specific data
    }

    public function initializeViaRequest(Tenant $tenant, Request $request): void
    {
        // Initialize via web request
    }

    public function rules(): array
    {
        return ['setting1' => 'required'];
    }

    public function getPriority(): int
    {
        return 100; // Higher priority runs first
    }

    public function getName(): string
    {
        return 'My Package Initializer';
    }

    public function getPackageName(): string
    {
        return 'mypackage';
    }
}

// Register the initializer
$manager = app(TenantInitializationManager::class);
$manager->register(new MyPackageInitializer());
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

### Package Creation

Create new packages with proper structure:

```bash
php artisan ingenius:create-package MyPackage
```

This creates a complete package structure with:
- Service providers
- Routes (web, api, tenant)
- Controllers, models, middleware
- Migrations (central and tenant)
- Configuration files
- Tests

## Helper Functions

The package provides several convenient helper functions:

```php
// Tenancy helpers
$tenant = tenant(); // Get current tenant
$tenancy = tenancy(); // Get tenancy instance
$isTenant = is_tenant_route(); // Check if current route is tenant

// Settings helpers
$value = settings('group', 'name', 'default');
$groupSettings = settings('group');
$settingsService = settings();

// User model helper
$userClass = central_user_class(); // Get central user model class
```

## Console Commands

The package provides numerous console commands organized by category:

### Installation and Setup
```bash
# Install the core package
php artisan ingenius:install

# Publish configurations from all packages
php artisan ingenius:publish:configs --force

# Publish tenant migrations from all packages
php artisan ingenius:publish:tenant-migrations --force

# Publish user model and migration
php artisan ingenius:user:publish --model --migration
```

### Package Management
```bash
# Create a new package
php artisan ingenius:create-package PackageName

# Initialize packages for a tenant
php artisan ingenius:initialize-packages tenant-id
php artisan ingenius:initialize-packages tenant-id --package=mypackage

# Create migrations in packages
php artisan ingenius:make:migration create_users_table mypackage --tenant
```

### Tenant Management
```bash
# Create a new tenant
php artisan ingenius:tenant:create --id=tenant1 --domain=tenant1.app --name="Tenant 1"

# Run migrations for tenants
php artisan ingenius:tenants:migrate --all
php artisan ingenius:tenants:migrate --tenants=tenant1,tenant2

# Rollback tenant migrations
php artisan ingenius:tenants:rollback --all
```

### Settings Management
```bash
# Register settings classes
php artisan settings:register

# Clear settings cache
php artisan settings:clear-cache
```

### Template and Feature Management
```bash
# Update basic template with current features
php artisan ingenius:template:update-basic-features
```

### User and Permission Management
```bash
# Add admin role to central user
php artisan ingenius:user:add-admin-role user@example.com

# Sync central permissions
php artisan ingenius:permissions:sync-central
```

### Development and Debugging
```bash
# Rollback package migrations
php artisan ingenius:rollback PackageName --steps=1
```

## Configuration

The package includes several configuration files:

### Core Configuration (`config/core.php`)
```php
return [
    'central_user_model' => 'App\\Models\\User',
    'central_auth_guard' => 'sanctum',
    'features' => [
        'central_auth' => true,
        'user_management' => true,
    ],
];
```

### Settings Configuration (`config/settings.php`)
```php
return [
    'cache' => [
        'enabled' => true,
        'prefix' => 'settings_',
        'ttl' => 86400,
    ],
    'encryption' => [
        'enabled' => true,
    ],
    'groups' => ['general', 'mail', 'invoices'],
    'settings_classes' => [
        // Register your settings classes here
    ],
];
```

### Sequences Configuration (`config/sequences.php`)
```php
return [
    'invoice' => [
        'prefix' => 'INV-',
        'start_number' => 1000,
        'random' => false,
    ],
];
```

### Package Configuration (`config/packages.php`)
```php
return [
    'basic_packages' => [
        'ingenius/auth' => '^0.0.1',
        'ingenius/orders' => '^0.0.1',
        // Add more packages
    ],
];
```

## API Endpoints

The package provides API endpoints for managing settings and templates:

### Settings API
```
GET /api/settings/{group} - Get all settings in a group
GET /api/settings/{group}/{name} - Get a specific setting
PUT /api/settings/{group}/{name} - Update a setting
DELETE /api/settings/{group}/{name} - Delete a setting
```

### Templates API
```
GET /api/templates - Get all templates
```

## Testing

The package includes comprehensive tests. Run them with:

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information. 