<?php

namespace Ingenius\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Ingenius\Core\Support\MigrationRegistry;

class PublishMigrationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ingenius:publish:migrations
                            {--package= : The package to publish migrations from}
                            {--force : Force overwrite of existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish normal (non-tenant) migrations from Ingenius packages to the main application';

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

        $packagePaths = $package
            ? [$package => $this->registry->getPackagePath($package)]
            : $this->registry->getPackagePaths();

        if (empty($packagePaths)) {
            $this->info('No packages found.');
            return 0;
        }

        $this->info('Publishing migrations from packages...');

        // Ensure the central migrations directory exists
        $centralMigrationsPath = database_path('migrations');
        if (!File::isDirectory($centralMigrationsPath)) {
            File::makeDirectory($centralMigrationsPath, 0755, true);
            $this->info("Created central migrations directory: {$centralMigrationsPath}");
        }

        $publishedCount = 0;
        $skippedCount = 0;

        foreach ($packagePaths as $packageName => $packagePath) {
            // Look for migrations in the package's database/migrations directory
            $migrationsPath = $packagePath . '/database/migrations';

            if (!File::isDirectory($migrationsPath)) {
                $this->line("<comment>No migrations directory found for package:</comment> {$packageName}");
                continue;
            }

            $this->line("<info>Publishing migrations from package:</info> {$packageName}");

            // Get all migration files directly in the migrations directory (not in tenant subdirectory)
            $migrationFiles = File::glob("{$migrationsPath}/*.php");

            if (empty($migrationFiles)) {
                $this->line("<comment>No migration files found in:</comment> {$migrationsPath}");
                continue;
            }

            foreach ($migrationFiles as $sourceFile) {
                // Skip any files in tenant subdirectory
                if (strpos($sourceFile, '/database/migrations/tenant/') !== false) {
                    continue;
                }

                $filename = basename($sourceFile);
                $destinationFile = "{$centralMigrationsPath}/{$filename}";

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

        $this->info("Migrations publishing completed: {$publishedCount} published, {$skippedCount} skipped.");

        return 0;
    }
}
