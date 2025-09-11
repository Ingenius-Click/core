<?php

namespace Ingenius\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CreatePackageCommand extends Command
{
    protected $signature = 'ingenius:create-package {name : The name of the package}';

    protected $description = 'Create a new Ingenius package with a structure similar to nwidart/Modules';

    public function handle()
    {
        $name = $this->argument('name');
        $nameLower = Str::lower($name);

        // Check if package already exists
        $packagePath = base_path("packages/ingenius/{$nameLower}");
        if (File::exists($packagePath)) {
            $this->error("Package {$name} already exists.");
            return 1;
        }

        $this->info("Creating package: {$name}");

        // Create package directory structure
        $this->createDirectoryStructure($packagePath, $name, $nameLower);

        // Create base files
        $this->createBaseFiles($packagePath, $name, $nameLower);

        $this->info("Package {$name} created successfully at {$packagePath}");
        $this->info("Don't forget to add it to your composer.json repositories!");

        return 0;
    }

    protected function createDirectoryStructure($packagePath, $name, $nameLower)
    {
        // Create main directories
        $directories = [
            '',
            'config',
            'routes',
            'src',
            'src/Providers',
            'src/Models',
            'src/Http',
            'src/Http/Controllers',
            'src/Http/Middleware',
            'src/Http/Requests',
            'src/Console',
            'src/Console/Commands',
            'src/Constants',
            'src/Interfaces',
            'src/Services',
            'src/Traits',
            'database',
            'database/migrations',
            'database/migrations/tenant',
            'database/seeders',
            'resources',
            'resources/views',
            'resources/js',
            'resources/css',
            'tests',
            'tests/Unit',
            'tests/Feature',
        ];

        foreach ($directories as $directory) {
            $path = $directory ? "{$packagePath}/{$directory}" : $packagePath;
            File::makeDirectory($path, 0755, true);
            $this->line("Created directory: {$path}");
        }
    }

    protected function createBaseFiles($packagePath, $name, $nameLower)
    {
        // Create composer.json
        $composerJson = $this->getComposerJsonTemplate($name, $nameLower);
        File::put("{$packagePath}/composer.json", $composerJson);
        $this->line("Created file: {$packagePath}/composer.json");

        // Create README.md
        $readme = $this->getReadmeTemplate($name);
        File::put("{$packagePath}/README.md", $readme);
        $this->line("Created file: {$packagePath}/README.md");

        // Create LICENSE
        $license = $this->getLicenseTemplate();
        File::put("{$packagePath}/LICENSE", $license);
        $this->line("Created file: {$packagePath}/LICENSE");

        // Create .gitignore
        $gitignore = $this->getGitignoreTemplate();
        File::put("{$packagePath}/.gitignore", $gitignore);
        $this->line("Created file: {$packagePath}/.gitignore");

        // Create Service Provider
        $serviceProvider = $this->getServiceProviderTemplate($name);
        File::put("{$packagePath}/src/Providers/{$name}ServiceProvider.php", $serviceProvider);
        $this->line("Created file: {$packagePath}/src/Providers/{$name}ServiceProvider.php");

        // Create Route Service Provider
        $routeServiceProvider = $this->getRouteServiceProviderTemplate($name);
        File::put("{$packagePath}/src/Providers/RouteServiceProvider.php", $routeServiceProvider);
        $this->line("Created file: {$packagePath}/src/Providers/RouteServiceProvider.php");

        // Create routes files
        $webRoutesTemplate = $this->getWebRoutesTemplate();
        File::put("{$packagePath}/routes/web.php", $webRoutesTemplate);
        $this->line("Created file: {$packagePath}/routes/web.php");

        $apiRoutesTemplate = $this->getApiRoutesTemplate();
        File::put("{$packagePath}/routes/api.php", $apiRoutesTemplate);
        $this->line("Created file: {$packagePath}/routes/api.php");

        $tenantRoutesTemplate = $this->getTenantRoutesTemplate();
        File::put("{$packagePath}/routes/tenant.php", $tenantRoutesTemplate);
        $this->line("Created file: {$packagePath}/routes/tenant.php");

        // Create config file
        $configTemplate = $this->getConfigTemplate($nameLower);
        File::put("{$packagePath}/config/{$nameLower}.php", $configTemplate);
        $this->line("Created file: {$packagePath}/config/{$nameLower}.php");

        // Create phpunit.xml
        $phpunitTemplate = $this->getPhpunitTemplate();
        File::put("{$packagePath}/phpunit.xml", $phpunitTemplate);
        $this->line("Created file: {$packagePath}/phpunit.xml");

        // Create TestCase.php
        $testCaseTemplate = $this->getTestCaseTemplate($name);
        File::put("{$packagePath}/tests/TestCase.php", $testCaseTemplate);
        $this->line("Created file: {$packagePath}/tests/TestCase.php");
    }

    protected function getComposerJsonTemplate($name, $nameLower)
    {
        return <<<JSON
{
    "name": "ingenius/{$nameLower}",
    "description": "Ingenius {$name} Package",
    "type": "library",
    "license": "MIT",
    "authors": [
        {
            "name": "Ingenius Team",
            "email": "info@ingeniusclick.com"
        }
    ],
    "require": {
        "php": "^8.2",
        "ingenius/core": "*"
    },
    "require-dev": {
        "orchestra/testbench": "^9.0",
        "phpunit/phpunit": "^11.0"
    },
    "autoload": {
        "psr-4": {
            "Ingenius\\\\{$name}\\\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Ingenius\\\\{$name}\\\\Tests\\\\": "tests/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "Ingenius\\\\{$name}\\\\Providers\\\\{$name}ServiceProvider"
            ]
        }
    },
    "minimum-stability": "stable",
    "prefer-stable": true
}
JSON;
    }

    protected function getReadmeTemplate($name)
    {
        return <<<MARKDOWN
# Ingenius {$name}

A package for the Ingenius Click platform.

## Installation

```bash
composer require ingenius/{$name}
```

## Usage

```php
// Usage examples will go here
```

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
MARKDOWN;
    }

    protected function getGitignoreTemplate()
    {
        return <<<GITIGNORE
/vendor
.phpunit.result.cache
composer.lock
.idea
.vscode
.DS_Store
GITIGNORE;
    }

    protected function getServiceProviderTemplate($name)
    {
        $nameLower = Str::lower($name);

        return <<<PHP
<?php

namespace Ingenius\\{$name}\\Providers;

use Illuminate\Support\ServiceProvider;
use Ingenius\\Core\\Traits\\RegistersMigrations;
use Ingenius\\Core\\Traits\\RegistersConfigurations;

class {$name}ServiceProvider extends ServiceProvider
{
    use RegistersMigrations, RegistersConfigurations;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        \$this->mergeConfigFrom(__DIR__.'/../../config/{$nameLower}.php', '{$nameLower}');
        
        // Register configuration with the registry
        \$this->registerConfig(__DIR__.'/../../config/{$nameLower}.php', '{$nameLower}', '{$nameLower}');
        
        // Register the route service provider
        \$this->app->register(RouteServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register migrations with the registry
        \$this->registerMigrations(__DIR__.'/../../database/migrations', '{$nameLower}');
        
        // Check if there's a tenant migrations directory and register it
        \$tenantMigrationsPath = __DIR__.'/../../database/migrations/tenant';
        if (is_dir(\$tenantMigrationsPath)) {
            \$this->registerTenantMigrations(\$tenantMigrationsPath, '{$nameLower}');
        }
        
        // Load views
        \$this->loadViewsFrom(__DIR__.'/../../resources/views', '{$nameLower}');
        
        // Load migrations
        \$this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        
        // Publish configuration
        \$this->publishes([
            __DIR__.'/../../config/{$nameLower}.php' => config_path('{$nameLower}.php'),
        ], '{$nameLower}-config');
        
        // Publish views
        \$this->publishes([
            __DIR__.'/../../resources/views' => resource_path('views/vendor/{$nameLower}'),
        ], '{$nameLower}-views');
        
        // Publish migrations
        \$this->publishes([
            __DIR__.'/../../database/migrations/' => database_path('migrations'),
        ], '{$nameLower}-migrations');
    }
}
PHP;
    }

    protected function getRouteServiceProviderTemplate($name)
    {
        return <<<PHP
<?php

namespace Ingenius\\{$name}\\Providers;

use Ingenius\Core\Http\Middleware\InitializeTenancyByDomain;
use Ingenius\Core\Http\Middleware\PreventAccessFromCentralDomains;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected string \$name = '{$name}';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        \$this->mapTenantRoutes();
        \$this->mapApiRoutes();
        \$this->mapWebRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')->group(function () {
            require __DIR__.'/../../routes/web.php';
        });
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapApiRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->name('api.')
            ->group(function () {
                require __DIR__.'/../../routes/api.php';
            });
    }

    /**
     * Define the "tenant" routes for the application.
     *
     * These routes all receive tenant specific middleware.
     */
    protected function mapTenantRoutes(): void
    {
        \$routeFile = __DIR__.'/../../routes/tenant.php';

        if (file_exists(\$routeFile)) {
            Route::middleware([
                InitializeTenancyByDomain::class,
                PreventAccessFromCentralDomains::class,
            ])->group(function () use (\$routeFile) {
                require \$routeFile;
            });
        }
    }
}
PHP;
    }

    protected function getWebRoutesTemplate()
    {
        return <<<PHP
<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your package.
| These routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group.
|
*/

// Route::get('example', function () {
//     return 'Hello from package web route!';
// });
PHP;
    }

    protected function getApiRoutesTemplate()
    {
        return <<<PHP
<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your package.
| These routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group and "api." name prefix.
|
*/

// Route::get('example', function () {
//     return response()->json(['message' => 'Hello from package API route!']);
// });
PHP;
    }

    protected function getTenantRoutesTemplate()
    {
        return <<<PHP
<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here is where you can register tenant-specific routes for your package.
| These routes are loaded by the RouteServiceProvider within a group which
| contains the tenant middleware for multi-tenancy support.
|
*/

// Route::get('tenant-example', function () {
//     return 'Hello from tenant-specific route! Current tenant: ' . tenant('id');
// });
PHP;
    }

    protected function getConfigTemplate($nameLower)
    {
        return <<<PHP
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can specify configuration options for the {$nameLower} package.
    |
    */

    'option' => 'value',
];
PHP;
    }

    protected function getPhpunitTemplate()
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="./vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
>
    <testsuites>
        <testsuite name="Unit">
            <directory suffix="Test.php">./tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory suffix="Test.php">./tests/Feature</directory>
        </testsuite>
    </testsuites>
    <coverage processUncoveredFiles="true">
        <include>
            <directory suffix=".php">./src</directory>
        </include>
    </coverage>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="CACHE_DRIVER" value="array"/>
        <env name="SESSION_DRIVER" value="array"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
    </php>
</phpunit>
XML;
    }

    protected function getTestCaseTemplate($name)
    {
        return <<<PHP
<?php

namespace Ingenius\\{$name}\\Tests;

use Ingenius\\{$name}\\Providers\\{$name}ServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders(\$app)
    {
        return [
            {$name}ServiceProvider::class,
        ];
    }
    
    protected function getEnvironmentSetUp(\$app)
    {
        // Setup default database to use sqlite :memory:
        \$app['config']->set('database.default', 'testbench');
        \$app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }
}
PHP;
    }

    protected function getLicenseTemplate()
    {
        return <<<LICENSE
MIT License

Copyright (c) 2024 Ingenius Team

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
LICENSE;
    }
}
