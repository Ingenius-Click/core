<?php

namespace Ingenius\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Ingenius\Core\Helpers\UserModelHelper;
use Ingenius\Core\Models\Template;
use Ingenius\Core\Services\FeatureManager;

class InstallCommand extends Command
{
    protected $signature = 'ingenius:install';

    protected $description = 'Install the IngeniusCore package';

    public function handle()
    {
        $this->info('Installing IngeniusCore package...');

        // Publish configuration
        $this->call('vendor:publish', [
            '--provider' => 'Ingenius\Core\Providers\CoreServiceProvider',
            '--tag' => 'ingenius-core-config',
        ]);

        // Publish migrations
        $this->call('vendor:publish', [
            '--provider' => 'Ingenius\Core\Providers\CoreServiceProvider',
            '--tag' => 'ingenius-core-migrations',
        ]);

        // Update bootstrap/app.php with required middleware
        $this->updateBootstrapAppFile();

        // Update auth.php with tenant configuration
        $this->updateAuthConfiguration();

        // Setup central User model
        $this->setupCentralUserModel();

        // Install basic packages
        $this->installBasicPackages();

        // Publish configurations from registry
        $this->call('ingenius:publish:configs', [
            '--force' => true,
        ]);

        // Publish tenant migrations from all packages
        $this->call('ingenius:publish:tenant-migrations', [
            '--force' => true,
        ]);

        $this->info('IngeniusCore package has been installed successfully.');

        // Run migrations
        if ($this->confirm('Would you like to run migrations now?', true)) {
            $this->call('migrate');
        }

        // Create basic user (in separate process)
        $this->info('Creating basic user...');
        $this->call('ingenius:create-user');

        // Create basic templates with default features (in separate process)
        $this->info('Creating basic templates...');
        $this->call('ingenius:create-templates');
    }

    /**
     * Update bootstrap/app.php file with required middleware configuration
     */
    protected function updateBootstrapAppFile()
    {
        $this->info('Checking bootstrap/app.php for required middleware configuration...');

        $appFilePath = base_path('bootstrap/app.php');

        if (!File::exists($appFilePath)) {
            $this->error('bootstrap/app.php not found. Please update it manually.');
            $this->showManualInstructions();
            return;
        }

        $content = File::get($appFilePath);

        // Check if the file contains the necessary import
        if (strpos($content, 'use Illuminate\Foundation\Configuration\Middleware;') === false) {
            $this->error('The bootstrap/app.php file does not contain the required Middleware class import.');
            $this->showManualInstructions();
            return;
        }

        // Check if the middleware configuration is already present
        if (
            strpos($content, 'Illuminate\Session\Middleware\StartSession::class') !== false &&
            strpos($content, 'Illuminate\Cookie\Middleware\EncryptCookies::class') !== false &&
            strpos($content, 'EnsureFrontendRequestsAreStateful::class') !== false
        ) {
            $this->info('Required middleware configuration already exists in bootstrap/app.php');
            return;
        }

        // Define the middleware configuration to add
        $middlewareConfig = <<<'EOT'
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            Illuminate\Session\Middleware\StartSession::class,
            Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
        $middleware->statefulApi();
        $middleware->group('universal', []);
    })
EOT;

        // Make a backup of the original file
        $backupPath = base_path('bootstrap/app.php.bak');
        File::copy($appFilePath, $backupPath);
        $this->info("Original file backed up to {$backupPath}");

        // Find the position to insert our new middleware configuration
        $insertPosition = false;

        // Define possible insertion points in order of preference
        $insertionPoints = [
            // After existing withMiddleware block
            function ($content) {
                if (preg_match('/->withMiddleware\s*\(\s*function\s*\(\s*Middleware\s*\$middleware\s*\)\s*{.*?}\s*\)/s', $content, $matches, PREG_OFFSET_CAPTURE)) {
                    $matchEnd = $matches[0][1] + strlen($matches[0][0]);
                    return $matchEnd;
                }
                return false;
            },
            // Before withExceptions
            function ($content) {
                $pos = strpos($content, '->withExceptions');
                return $pos !== false ? $pos : false;
            },
            // Before create()
            function ($content) {
                $pos = strpos($content, '->create()');
                return $pos !== false ? $pos : false;
            },
            // Before create();
            function ($content) {
                $pos = strpos($content, '->create();');
                return $pos !== false ? $pos : false;
            }
        ];

        // Try each insertion point until we find a valid one
        foreach ($insertionPoints as $finder) {
            $position = $finder($content);
            if ($position !== false) {
                $insertPosition = $position;
                break;
            }
        }

        if ($insertPosition === false) {
            $this->warn('Could not automatically update bootstrap/app.php. The file structure may be different than expected.');
            $this->showManualInstructions();
            return;
        }

        // Insert our middleware configuration at the found position
        $updatedContent = substr($content, 0, $insertPosition) .
            $middlewareConfig .
            substr($content, $insertPosition);

        // Write the updated content back to the file
        try {
            File::put($appFilePath, $updatedContent);
            $this->info('bootstrap/app.php updated successfully with required middleware.');
        } catch (\Exception $e) {
            $this->error('Failed to update bootstrap/app.php: ' . $e->getMessage());
            $this->showManualInstructions();
        }
    }

    /**
     * Update auth.php file with tenant_users provider and tenant guard
     */
    protected function updateAuthConfiguration()
    {
        $this->info('Checking auth.php for tenant configuration...');

        $authFilePath = config_path('auth.php');

        if (!File::exists($authFilePath)) {
            $this->error('auth.php not found. Please update it manually.');
            $this->showAuthManualInstructions();
            return;
        }

        $content = File::get($authFilePath);

        // Check if tenant configuration is already present
        $hasTenantGuard = strpos($content, "'tenant' => [") !== false;
        $hasTenantProvider = strpos($content, "'tenant_users' => [") !== false;

        if ($hasTenantGuard && $hasTenantProvider) {
            $this->info('Tenant configuration already exists in auth.php');
            return;
        }

        // Make a backup of the original file
        $backupPath = config_path('auth.php.bak');
        File::copy($authFilePath, $backupPath);
        $this->info("Original auth.php backed up to {$backupPath}");

        // Parse the PHP file to get the array structure
        $authConfig = include($authFilePath);

        // Add tenant guard if it doesn't exist
        if (!isset($authConfig['guards']['tenant'])) {
            $authConfig['guards']['tenant'] = [
                'driver' => 'session',
                'provider' => 'tenant_users',
            ];
        }

        // Add tenant_users provider if it doesn't exist
        if (!isset($authConfig['providers']['tenant_users'])) {
            $authConfig['providers']['tenant_users'] = [
                'driver' => 'eloquent',
                'model' => \Ingenius\Auth\Models\User::class,
            ];
        }

        // Add tenant_users password broker if it doesn't exist
        if (!isset($authConfig['passwords']['tenant_users'])) {
            $authConfig['passwords']['tenant_users'] = [
                'provider' => 'tenant_users',
                'table' => 'password_reset_tokens',
                'expire' => 60,
                'throttle' => 60,
            ];
        }

        try {
            // Convert the array back to a PHP file
            $updatedContent = "<?php\n\nreturn " . $this->varExport($authConfig, true) . ";\n";

            // Write the updated content back to the file
            File::put($authFilePath, $updatedContent);
            $this->info('auth.php updated successfully with tenant configuration.');
        } catch (\Exception $e) {
            $this->error('Failed to update auth.php: ' . $e->getMessage());
            $this->showAuthManualInstructions();
        }
    }

    /**
     * Setup central User model
     */
    protected function setupCentralUserModel()
    {
        $this->info('Setting up central User model for authentication...');

        $status = UserModelHelper::getUserModelStatus();

        // Show current status
        $this->line('Current User Model Status:');
        $this->line('  Laravel User Model Exists: ' . ($status['laravel_model_exists'] ? 'Yes' : 'No'));
        $this->line('  Users Table Exists: ' . ($status['users_table_exists'] ? 'Yes' : 'No'));
        $this->line('  Laravel Migration Exists: ' . ($status['laravel_migration_exists'] ? 'Yes' : 'No'));
        $this->line('');

        // If Laravel User model exists and users table exists, offer choice
        if ($status['laravel_model_exists'] && $status['users_table_exists']) {
            $this->info('You already have a Laravel User model and users table.');
            $this->line('The core package will use your existing User model by default.');

            if ($this->confirm('Would you like to publish the core User model as an alternative?', false)) {
                $this->call('ingenius:publish:user-model', ['--model' => true]);
            }

            $this->info('In order to use the Laravel User model, this model needs to use the HasApiTokens and HasRoles traits.');

            return;
        }

        // If Laravel User model exists but no users table
        if ($status['laravel_model_exists'] && !$status['users_table_exists']) {
            $this->info('You have a Laravel User model but no users table.');

            if ($status['laravel_migration_exists']) {
                $this->line('Laravel user migration found. You can run migrations to create the table.');
            } else {
                $this->warn('No Laravel user migration found.');

                if ($this->confirm('Would you like to publish the core user migration?', true)) {
                    $this->call('ingenius:publish:user-model', ['--migration' => true]);
                }
            }
            return;
        }

        // If no Laravel User model exists
        if (!$status['laravel_model_exists']) {
            $this->warn('No Laravel User model found.');

            $choice = $this->choice(
                'How would you like to set up the User model?',
                [
                    'Create standard Laravel User model',
                    'Use and publish core package User model',
                    'Skip for now'
                ],
                0
            );

            switch ($choice) {
                case 'Create standard Laravel User model':
                    $this->createStandardLaravelUserModel();
                    break;

                case 'Use and publish core package User model':
                    $this->call('ingenius:publish:user-model');
                    break;

                case 'Skip for now':
                    $this->comment('Skipping User model setup. You can set it up later using:');
                    $this->line('  php artisan ingenius:publish:user-model');
                    break;
            }
            return;
        }

        // Default case - everything looks good
        $this->info('User model setup completed.');
    }

    /**
     * Create standard Laravel User model
     */
    protected function createStandardLaravelUserModel()
    {
        $userModelPath = app_path('Models/User.php');
        $userModelContent = <<<'EOT'
<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
EOT;

        // Ensure the Models directory exists
        $modelsDir = app_path('Models');
        if (!File::isDirectory($modelsDir)) {
            File::makeDirectory($modelsDir, 0755, true);
        }

        try {
            File::put($userModelPath, $userModelContent);
            $this->info('Standard Laravel User model created successfully at ' . $userModelPath);

            // Ask about migration
            if ($this->confirm('Would you like to publish the core user migration?', true)) {
                $this->call('ingenius:publish:user-model', ['--migration' => true]);
            }
        } catch (\Exception $e) {
            $this->error('Failed to create User model: ' . $e->getMessage());
        }
    }

    /**
     * Format array export with proper indentation
     */
    protected function varExport($var, $indent = true)
    {
        switch (gettype($var)) {
            case 'string':
                return "'" . addcslashes($var, "'\\\0..\37") . "'";
            case 'array':
                $indexed = array_keys($var) === range(0, count($var) - 1);
                $r = [];
                foreach ($var as $key => $value) {
                    $r[] = ($indent ? '    ' : '')
                        . ($indexed ? '' : $this->varExport($key) . ' => ')
                        . $this->varExport($value, $indent);
                }
                return "[\n" . implode(",\n", $r) . "\n" . ($indent ? '' : '    ') . "]";
            case 'boolean':
                return $var ? 'true' : 'false';
            case 'NULL':
                return 'null';
            case 'integer':
            case 'double':
            default:
                return $var;
        }
    }

    /**
     * Show manual instructions for updating bootstrap/app.php
     */
    protected function showManualInstructions()
    {
        $this->line('');
        $this->line('Please manually update your bootstrap/app.php file to include the following middleware configuration:');
        $this->line('');
        $this->line('->withMiddleware(function (Middleware $middleware) {');
        $this->line('    $middleware->api(prepend: [');
        $this->line('        Illuminate\Session\Middleware\StartSession::class,');
        $this->line('        Illuminate\Cookie\Middleware\EncryptCookies::class,');
        $this->line('        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,');
        $this->line('    ]);');
        $this->line('    $middleware->statefulApi();');
        $this->line('    $middleware->group(\'universal\', []);');
        $this->line('})');
        $this->line('');
        $this->line('Make sure you have the following import at the top of the file:');
        $this->line('use Illuminate\Foundation\Configuration\Middleware;');
        $this->line('');
    }

    /**
     * Show manual instructions for updating auth.php
     */
    protected function showAuthManualInstructions()
    {
        $this->line('');
        $this->line('Please manually update your auth.php file to include the following tenant configuration:');
        $this->line('');
        $this->line("// In the 'guards' array:");
        $this->line("'tenant' => [");
        $this->line("    'driver' => 'session',");
        $this->line("    'provider' => 'tenant_users',");
        $this->line("],");
        $this->line('');
        $this->line("// In the 'providers' array:");
        $this->line("'tenant_users' => [");
        $this->line("    'driver' => 'eloquent',");
        $this->line("    'model' => \\Ingenius\\Auth\\Models\\User::class,");
        $this->line("],");
        $this->line('');
        $this->line("// In the 'passwords' array:");
        $this->line("'tenant_users' => [");
        $this->line("    'provider' => 'tenant_users',");
        $this->line("    'table' => 'password_reset_tokens',");
        $this->line("    'expire' => 60,");
        $this->line("    'throttle' => 60,");
        $this->line("],");
        $this->line('');
    }

    /**
     * Install basic packages defined in the config
     */
    protected function installBasicPackages()
    {
        $basicPackages = config('packages.basic_packages', []);

        if (empty($basicPackages)) {
            $this->comment('No basic packages defined in the configuration.');
            return;
        }

        $this->info('The following basic packages are available for installation:');
        foreach ($basicPackages as $package => $version) {
            $this->line("  - {$package}" . ($version ? " ({$version})" : ''));
        }
        $this->newLine();

        $prompt = config('packages.installation_prompt', 'Would you like to install the basic packages?');

        if (!$this->confirm($prompt, true)) {
            $this->comment('Skipping basic packages installation.');
            return;
        }

        $this->info('Installing basic packages...');

        $successCount = 0;
        $failureCount = 0;
        $totalPackages = count($basicPackages);
        $currentPackage = 0;

        foreach ($basicPackages as $package => $version) {
            $currentPackage++;
            // Format the package requirement correctly for composer
            $packageRequirement = $package;
            if (!empty($version)) {
                $packageRequirement = "{$package}:{$version}";
            }

            $command = "composer require {$packageRequirement}";

            $this->info("\nInstalling package [{$currentPackage}/{$totalPackages}]: {$package}" . ($version ? " ({$version})" : ''));

            try {
                $process = Process::timeout(300)->run($command, function ($type, $output) {
                    if ($type === 'out') {
                        $this->output->write($output);
                    } else {
                        $this->info($output);
                    }
                });

                if ($process->successful()) {
                    $this->info("✓ Successfully installed {$package}");
                    $successCount++;
                } else {
                    $this->error("✗ Failed to install {$package}");
                    $failureCount++;
                    if ($this->confirm('Continue with remaining packages?', true) === false) {
                        break;
                    }
                }
            } catch (\Exception $e) {
                $this->error("✗ Error installing {$package}: " . $e->getMessage());
                $failureCount++;
                if ($this->confirm('Continue with remaining packages?', true) === false) {
                    break;
                }
            }
        }

        $this->newLine();
        $this->info("Basic packages installation completed:");
        $this->line("  - Successfully installed: {$successCount}");
        if ($failureCount > 0) {
            $this->line("  - Failed installations: {$failureCount}");
        }

        // Run composer dump-autoload if any packages were successfully installed
        if ($successCount > 0) {
            $this->info('Running composer dump-autoload...');
            try {
                $process = Process::timeout(60)->run('composer dump-autoload', function ($type, $output) {
                    if ($type === 'out') {
                        $this->output->write($output);
                    } else {
                        $this->info($output);
                    }
                });

                if ($process->successful()) {
                    $this->info('✓ Composer autoload regenerated successfully');
                } else {
                    $this->warn('⚠ Failed to regenerate composer autoload');
                }
            } catch (\Exception $e) {
                $this->warn('⚠ Error running composer dump-autoload: ' . $e->getMessage());
            }

            // Run package discovery to ensure newly installed packages are discovered
            $this->info('Running package discovery...');
            try {
                $this->call('package:discover');
                $this->info('✓ Package discovery completed successfully');
            } catch (\Exception $e) {
                $this->warn('⚠ Error running package discovery: ' . $e->getMessage());
            }
        }
    }
}
