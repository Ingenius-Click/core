<?php

namespace Ingenius\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Ingenius\Core\Models\Tenant;
use Ingenius\Core\Support\TenantInitializationManager;

class InitializePackageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ingenius:initialize-packages 
                            {tenant : The tenant ID to initialize packages for}
                            {--package= : Specific package to initialize}
                            {--dry-run : Show what would be initialized without executing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize packages for a specific tenant';

    /**
     * Create a new command instance.
     */
    public function __construct(
        protected TenantInitializationManager $initializationManager
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->argument('tenant');
        $packageName = $this->option('package');
        $dryRun = $this->option('dry-run');

        // Validate tenant exists
        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            $this->error("Tenant with ID '{$tenantId}' not found.");
            return 1;
        }

        $this->info("Target tenant: {$tenant->getName()} (ID: {$tenantId})");

        // Get available packages
        $availablePackages = $this->initializationManager->getAvailablePackages();

        if (empty($availablePackages)) {
            $this->error('No package initializers found.');
            return 1;
        }

        // Handle package selection
        if ($packageName) {
            // Validate specified package exists
            if (!in_array($packageName, $availablePackages)) {
                $this->error("Package '{$packageName}' not found.");
                $this->info('Available packages: ' . implode(', ', $availablePackages));
                return 1;
            }
            $packagesToInitialize = [$packageName];
        } else {
            // Interactive package selection
            $this->info('Available packages:');
            foreach ($availablePackages as $package) {
                $initializers = $this->initializationManager->getInitializersByPackage($package);
                $this->line("  - {$package} ({$initializers->count()} initializer(s))");
            }

            $choices = array_merge(['all'], $availablePackages);
            $selection = $this->choice(
                'Which package(s) would you like to initialize?',
                $choices,
                'all'
            );

            $packagesToInitialize = $selection === 'all' ? $availablePackages : [$selection];
        }

        // Show what will be initialized
        $this->info('Packages to initialize: ' . implode(', ', $packagesToInitialize));

        $totalInitializers = 0;
        foreach ($packagesToInitialize as $package) {
            $initializers = $this->initializationManager->getInitializersByPackage($package);
            $totalInitializers += $initializers->count();

            if ($dryRun) {
                $this->line("Package '{$package}':");
                foreach ($initializers->sortByDesc('getPriority') as $initializer) {
                    $this->line("  - {$initializer->getName()} (Priority: {$initializer->getPriority()})");
                }
            }
        }

        if ($dryRun) {
            $this->info("Dry run complete. {$totalInitializers} initializer(s) would be executed.");
            return 0;
        }

        // Confirm execution unless in non-interactive mode
        if (!$this->option('no-interaction')) {
            if (!$this->confirm("Initialize {$totalInitializers} initializer(s) for tenant '{$tenantId}'?", true)) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        // Execute initialization
        $successCount = 0;
        $errorCount = 0;

        if (in_array('all', $packagesToInitialize)) {
            // Initialize all packages at once
            $this->info("Initializing all packages...");

            try {
                $this->initializationManager->initializeTenant($tenant, $this, []);
                $successCount = $totalInitializers;
                $this->info("All packages initialization completed.");
            } catch (\Exception $e) {
                $this->error("Failed to initialize all packages: " . $e->getMessage());
                $errorCount = $totalInitializers;
            }
        } else {
            // Initialize specific packages
            foreach ($packagesToInitialize as $package) {
                $this->info("Initializing package: {$package}");

                try {
                    // Use the TenantInitializationManager's method which properly handles tenancy context
                    $this->initializationManager->initializeTenantByPackage($tenant, $this, $package);

                    $packageInitializers = $this->initializationManager->getInitializersByPackage($package);
                    $successCount += $packageInitializers->count();

                    $this->info("Package '{$package}' initialization completed.");
                } catch (\Exception $e) {
                    $this->error("Failed to initialize package '{$package}': " . $e->getMessage());

                    $packageInitializers = $this->initializationManager->getInitializersByPackage($package);
                    $errorCount += $packageInitializers->count();
                }
            }
        }

        // Summary
        $this->newLine();
        if ($successCount > 0) {
            $this->info("Successfully initialized {$successCount} package initializer(s).");
        }

        if ($errorCount > 0) {
            $this->error("Failed to initialize {$errorCount} package initializer(s).");
        }

        $this->info('Package initialization completed.');

        return $errorCount > 0 ? 1 : 0;
    }
}
