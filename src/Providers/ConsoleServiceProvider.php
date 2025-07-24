<?php

namespace Ingenius\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Ingenius\Core\Console\Commands\AddAdminRoleToCentralUserCommand;
use Ingenius\Core\Console\Commands\ClearSettingsCacheCommand;
use Ingenius\Core\Console\Commands\CreatePackageCommand;
use Ingenius\Core\Console\Commands\CreateTenantCommand;
use Ingenius\Core\Console\Commands\CreateTemplatesCommand;
use Ingenius\Core\Console\Commands\InitializePackageCommand;
use Ingenius\Core\Console\Commands\InstallCommand;
use Ingenius\Core\Console\Commands\MakeMigrationCommand;
use Ingenius\Core\Console\Commands\PublishConfigsCommand;
use Ingenius\Core\Console\Commands\PublishMigrationsCommand;
use Ingenius\Core\Console\Commands\PublishTenantMigrationsCommand;
use Ingenius\Core\Console\Commands\PublishUserModelCommand;
use Ingenius\Core\Console\Commands\RegisterSettingsCommand;
use Ingenius\Core\Console\Commands\RollbackCommand;
use Ingenius\Core\Console\Commands\SyncCentralPermissionsCommand;
use Ingenius\Core\Console\Commands\TenantMigrateCommand;
use Ingenius\Core\Console\Commands\TenantRollbackCommand;
use Ingenius\Core\Console\Commands\UpdateBasicTemplateFeaturesCommand;

class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $commands = [
        InstallCommand::class,
        CreatePackageCommand::class,
        CreateTenantCommand::class,
        CreateTemplatesCommand::class,
        InitializePackageCommand::class,
        PublishMigrationsCommand::class,
        TenantMigrateCommand::class,
        MakeMigrationCommand::class,
        RollbackCommand::class,
        TenantRollbackCommand::class,
        PublishConfigsCommand::class,
        PublishTenantMigrationsCommand::class,
        PublishUserModelCommand::class,
        RegisterSettingsCommand::class,
        ClearSettingsCacheCommand::class,
        SyncCentralPermissionsCommand::class,
        AddAdminRoleToCentralUserCommand::class,
        UpdateBasicTemplateFeaturesCommand::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->commands($this->commands);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
