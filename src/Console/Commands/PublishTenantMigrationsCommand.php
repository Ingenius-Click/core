<?php

namespace Ingenius\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Ingenius\Core\Support\MigrationRegistry;

class PublishTenantMigrationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ingenius:publish:tenant-migrations
                            {--package= : The package to publish tenant migrations for}
                            {--force : Force overwrite of existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish tenant migrations from all packages to the central tenant migrations directory';

    /**
     * The migration registry instance.
     *
     * @var \Ingenius\Core\Support\MigrationRegistry
     */
    protected $registry;

    /**
     * Create a new command instance.
     *
     * @param  \Ingenius\Core\Support\MigrationRegistry  $registry
     * @return void
     */
    public function __construct(MigrationRegistry $registry)
    {
        parent::__construct();

        $this->registry = $registry;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $package = $this->option('package');
        $force = $this->option('force');

        $migrations = $package
            ? $this->registry->tenantForPackage($package)
            : $this->registry->allTenant();

        if (empty($migrations)) {
            $this->info('No tenant migrations found to publish.');
            return 0;
        }

        $this->info('Publishing tenant migrations from all packages...');

        // Ensure the central tenant migrations directory exists
        $centralTenantMigrationsPath = database_path('migrations/tenant');
        if (!File::isDirectory($centralTenantMigrationsPath)) {
            File::makeDirectory($centralTenantMigrationsPath, 0755, true);
            $this->info("Created central tenant migrations directory: {$centralTenantMigrationsPath}");
        }

        $publishedCount = 0;
        $skippedCount = 0;

        foreach ($migrations as $migration) {
            $sourcePath = $migration['path'];
            $package = $migration['package'];

            $this->line("<info>Publishing tenant migrations from package:</info> {$package}");

            // Get all migration files from the source path
            $migrationFiles = File::glob("{$sourcePath}/*.php");

            if (empty($migrationFiles)) {
                $this->line("<comment>No migration files found in:</comment> {$sourcePath}");
                continue;
            }

            foreach ($migrationFiles as $sourceFile) {
                $filename = basename($sourceFile);
                $destinationFile = "{$centralTenantMigrationsPath}/{$filename}";

                // Check if the file already exists and if we should overwrite it
                if (File::exists($destinationFile) && !$force) {
                    $this->line("<comment>Skipped:</comment> {$filename} (already exists, use --force to overwrite)");
                    $skippedCount++;
                    continue;
                }

                // Copy the migration file
                File::copy($sourceFile, $destinationFile);
                $this->line("<info>Published:</info> {$filename}");
                $publishedCount++;
            }
        }

        $this->info("Tenant migrations publishing completed: {$publishedCount} published, {$skippedCount} skipped.");

        return 0;
    }
}
