<?php

namespace Ingenius\Core\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Ingenius\Core\Features\UpdateSettingsFeature;
use Ingenius\Core\Features\ViewNotificationsFeature;
use Ingenius\Core\Features\ManageNotificationsFeature;
use Ingenius\Core\Features\ManageNotificationTemplatesFeature;
use Ingenius\Core\Policies\SettingsPolicy;
use Ingenius\Core\Policies\NotificationConfigurationPolicy;
use Ingenius\Core\Models\NotificationConfiguration;
use Ingenius\Core\Services\EmailNotificationService;
use Ingenius\Core\Services\FeatureManager;
use Ingenius\Core\Services\AbstractTableHandler;
use Ingenius\Core\Services\GenericTableHandler;
use Ingenius\Core\Models\Settings;
use Ingenius\Core\Services\PackageHookManager;
use Ingenius\Core\Services\TenantMiddlewareManager;
use Ingenius\Core\Services\EventRegistryService;
use Ingenius\Core\Services\ChannelRegistryService;
use Ingenius\Core\Support\ConfigRegistry;
use Ingenius\Core\Support\MigrationRegistry;
use Ingenius\Core\Support\PermissionsManager;
use Ingenius\Core\Support\TenantInitializationManager;
use Ingenius\Core\Services\StoreConfigurationManager;
use Ingenius\Core\Traits\RegistersConfigurations;
use Ingenius\Core\Traits\RegistersMigrations;
use Ingenius\Core\Interfaces\HasCustomerProfile;
use Stancl\Tenancy\Tenancy;
use InvalidArgumentException;

class CoreServiceProvider extends ServiceProvider
{
    use RegistersMigrations, RegistersConfigurations;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register sub-providers
        $this->app->register(MacrosServiceProvider::class);
        $this->app->register(TenancyServiceProvider::class);
        $this->app->register(PermissionServiceProvider::class);
        $this->app->register(NotificationsPermissionServiceProvider::class);
        $this->app->register(MediaLibraryServiceProvider::class);
        $this->app->register(ConsoleServiceProvider::class);
        $this->app->register(SequenceGeneratorServiceProvider::class);
        $this->app->register(SettingsServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(EventServiceProvider::class);

        // Register the PermissionsManager singleton
        $this->app->singleton(PermissionsManager::class, function ($app) {
            return new PermissionsManager();
        });

        // Register the MigrationRegistry singleton
        $this->app->singleton(MigrationRegistry::class, function ($app) {
            return new MigrationRegistry();
        });

        // Register the ConfigRegistry singleton
        $this->app->singleton(ConfigRegistry::class, function ($app) {
            return new ConfigRegistry();
        });

        // Register the TenantInitializationManager singleton
        $this->app->singleton(TenantInitializationManager::class, function ($app) {
            return new TenantInitializationManager($app->make(Tenancy::class));
        });

        $this->app->singleton(FeatureManager::class, function ($app) {
            return new FeatureManager();
        });

        $this->app->afterResolving(FeatureManager::class, function (FeatureManager $manager) {
            $manager->register(new UpdateSettingsFeature());
            $manager->register(new ViewNotificationsFeature());
            $manager->register(new ManageNotificationsFeature());
            $manager->register(new ManageNotificationTemplatesFeature());
        });

        // Register the PackageHookManager singleton
        $this->app->singleton(PackageHookManager::class, function ($app) {
            return new PackageHookManager();
        });

        // Register the StoreConfigurationManager singleton
        $this->app->singleton(StoreConfigurationManager::class, function ($app) {
            return new StoreConfigurationManager();
        });

        // Register the TenantMiddlewareManager singleton
        $this->app->singleton(TenantMiddlewareManager::class, function ($app) {
            return new TenantMiddlewareManager();
        });

        // Register the EventRegistryService singleton
        $this->app->singleton(EventRegistryService::class, fn() => new EventRegistryService());

        // Register the ChannelRegistryService singleton
        $this->app->singleton(ChannelRegistryService::class, fn() => new ChannelRegistryService());

        $this->app->afterResolving(ChannelRegistryService::class, function (ChannelRegistryService $registry) {
            // Register built-in channels
            $registry->register('email', EmailNotificationService::class);
        });

        // Register the table handler based on configuration
        $this->app->bind(AbstractTableHandler::class, function ($app) {
            $handlerClass = config('core.table_handler', GenericTableHandler::class);

            // Validate that the configured class exists
            if (!class_exists($handlerClass)) {
                throw new InvalidArgumentException("Table handler class [{$handlerClass}] does not exist.");
            }

            // Validate that the configured class extends AbstractTableHandler
            if (!is_subclass_of($handlerClass, AbstractTableHandler::class)) {
                throw new InvalidArgumentException("Table handler class [{$handlerClass}] must extend AbstractTableHandler.");
            }

            // Instantiate the configured table handler
            return new $handlerClass();
        });

        // Register core configurations
        $configPath = __DIR__ . '/../../config';
        $this->registerConfig($configPath . '/core.php', 'core', 'core');
        $this->registerConfig($configPath . '/tenancy.php', 'tenancy', 'core');
        $this->registerConfig($configPath . '/permission.php', 'permission', 'core');
        $this->registerConfig($configPath . '/media-library.php', 'media-library', 'core');
        $this->registerConfig($configPath . '/packages.php', 'packages', 'core');
        $this->registerConfig($configPath . '/sequences.php', 'sequences', 'core');
        $this->registerConfig($configPath . '/settings.php', 'settings', 'core');

        // Merge configurations
        $this->mergeConfigFrom(__DIR__ . '/../../config/core.php', 'core');
        $this->mergeConfigFrom(__DIR__ . '/../../config/tenancy.php', 'tenancy');
        $this->mergeConfigFrom(__DIR__ . '/../../config/permission.php', 'permission');
        $this->mergeConfigFrom(__DIR__ . '/../../config/media-library.php', 'media-library');
        $this->mergeConfigFrom(__DIR__ . '/../../config/packages.php', 'packages');
        $this->mergeConfigFrom(__DIR__ . '/../../config/sequences.php', 'sequences');
        $this->mergeConfigFrom(__DIR__ . '/../../config/settings.php', 'settings');

        // Register settings classes with the core settings system
        $this->registerSettingsClasses();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Validate tenant user model implements HasCustomerProfile
        $this->validateTenantUserContract();

        // Register migrations with the registry
        $this->registerMigrations(__DIR__ . '/../../database/migrations', 'core');

        // Check if there's a tenant migrations directory and register it
        $tenantMigrationsPath = __DIR__ . '/../../database/migrations/tenant';
        if (is_dir($tenantMigrationsPath)) {
            $this->registerTenantMigrations($tenantMigrationsPath, 'core');
        }

        // Register the TenantHasFeature middleware
        $this->registerMiddlewares();

        $this->registerPolicies();

        // Register tenant initializer
        $this->registerTenantInitializer();

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'core');

        // Publish configurations
        $this->publishes([
            __DIR__ . '/../../config/core.php' => config_path('core.php'),
            __DIR__ . '/../../config/tenancy.php' => config_path('tenancy.php'),
            __DIR__ . '/../../config/permission.php' => config_path('permission.php'),
            __DIR__ . '/../../config/media-library.php' => config_path('media-library.php'),
            __DIR__ . '/../../config/packages.php' => config_path('packages.php'),
            __DIR__ . '/../../config/sequences.php' => config_path('sequences.php'),
            __DIR__ . '/../../config/settings.php' => config_path('settings.php'),
        ], 'ingenius-core-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../../database/migrations' => database_path('migrations'),
        ], 'ingenius-core-migrations');

        // Publish User model and migration
        $this->publishes([
            __DIR__ . '/../../src/Models/User.php' => app_path('Models/CoreUser.php'),
        ], 'ingenius-core-user-model');

        $this->publishes([
            __DIR__ . '/../../database/migrations/2024_01_01_000001_create_central_users_table.php' => database_path('migrations/' . date('Y_m_d_His') . '_create_central_users_table.php'),
        ], 'ingenius-core-user-migration');

        // Publish views
        $this->publishes([
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/core'),
        ], 'ingenius-core-views');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Load all registered configurations
        app(ConfigRegistry::class)->loadAll();
    }

    /**
     * Register middlewares
     */
    protected function registerMiddlewares(): void
    {
        $router = $this->app['router'];

        $router->aliasMiddleware('tenant.has.feature', \Ingenius\Core\Http\Middleware\TenantHasFeature::class);
    }

    protected function registerPolicies(): void
    {
        Gate::policy(Settings::class, SettingsPolicy::class);
        Gate::policy(NotificationConfiguration::class, NotificationConfigurationPolicy::class);
    }

    /**
     * Register tenant initializer
     */
    protected function registerTenantInitializer(): void
    {
        $this->app->afterResolving(TenantInitializationManager::class, function (TenantInitializationManager $manager) {
            $initializer = $this->app->make(\Ingenius\Core\Initializers\CustomizeInitializer::class);
            $manager->register($initializer);
        });
    }

    /**
     * Validate that the tenant user model implements HasCustomerProfile interface.
     * This ensures customer data (lastname, address, phone) is available.
     *
     * @throws \InvalidArgumentException
     */
    protected function validateTenantUserContract(): void
    {
        $tenantUserClass = tenant_user_class();

        // Check if the class exists
        if (!class_exists($tenantUserClass)) {
            throw new InvalidArgumentException(
                "Tenant user model [{$tenantUserClass}] does not exist. " .
                "Please check your core.tenant_user_model configuration."
            );
        }

        // Check if it implements HasCustomerProfile
        $reflection = new \ReflectionClass($tenantUserClass);

        if (!$reflection->implementsInterface(HasCustomerProfile::class)) {
            throw new InvalidArgumentException(
                "Tenant user model [{$tenantUserClass}] must implement " .
                HasCustomerProfile::class . " interface.\n\n" .
                "This ensures customer profile data (firstname, lastname, address, phone) is available.\n\n" .
                "Add to your user model:\n" .
                "  implements " . HasCustomerProfile::class . "\n\n" .
                "Or use the provided trait:\n" .
                "  use HasCustomerProfileTrait;\n\n" .
                "Example:\n" .
                "  class User extends Authenticatable implements HasCustomerProfile\n" .
                "  {\n" .
                "      use HasCustomerProfileTrait;\n" .
                "  }"
            );
        }
    }

    /**
     * Register settings classes with the core settings system.
     */
    protected function registerSettingsClasses(): void
    {
        // Get existing settings classes from core config
        $coreSettingsClasses = Config::get('settings.settings_classes', []);

        // Get core package settings classes
        $packageSettingsClasses = Config::get('core.settings_classes', []);

        // Merge and update the core settings classes
        $mergedSettingsClasses = array_merge($coreSettingsClasses, $packageSettingsClasses);

        // Update the core settings config
        Config::set('settings.settings_classes', $mergedSettingsClasses);
    }
}
