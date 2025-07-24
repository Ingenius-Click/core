<?php

namespace Ingenius\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use Ingenius\Core\Support\MigrationRegistry;

class MakeMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ingenius:make:migration 
                            {name : The name of the migration}
                            {package : The name of the package}
                            {--tenant : Create a tenant migration}
                            {--create= : The table to be created}
                            {--table= : The table to be updated}
                            {--path= : The location where the migration file should be created}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new migration file in an Ingenius package';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The Composer instance.
     *
     * @var \Illuminate\Support\Composer
     */
    protected $composer;

    /**
     * The migration registry instance.
     *
     * @var \Ingenius\Core\Support\MigrationRegistry
     */
    protected $registry;

    /**
     * Create a new command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  \Illuminate\Support\Composer  $composer
     * @param  \Ingenius\Core\Support\MigrationRegistry  $registry
     * @return void
     */
    public function __construct(Filesystem $files, Composer $composer, MigrationRegistry $registry)
    {
        parent::__construct();

        $this->files = $files;
        $this->composer = $composer;
        $this->registry = $registry;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->argument('name');
        $package = Str::lower($this->argument('package'));
        $isTenant = $this->option('tenant');

        // Check if the package exists
        if (!$this->registry->hasPackage($package)) {
            $this->error("Package '{$package}' is not registered. Make sure the package exists and its service provider is registered.");
            return 1;
        }

        // Determine the path where the migration should be created
        $path = $this->getMigrationPath($package, $isTenant);

        // Create the migration
        $this->writeMigration($name, $path);

        $this->composer->dumpAutoloads();

        return 0;
    }

    /**
     * Write the migration file to disk.
     *
     * @param  string  $name
     * @param  string  $path
     * @return void
     */
    protected function writeMigration($name, $path)
    {
        $table = $this->option('table');
        $create = $this->option('create');

        if (! $table && is_string($create)) {
            $table = $create;
            $create = true;
        }

        // Create the directory if it doesn't exist
        if (! is_dir($path)) {
            $this->files->makeDirectory($path, 0755, true);
        }

        // Get the migration creator
        $creator = app(MigrationCreator::class);

        $file = $creator->create(
            $name,
            $path,
            $table,
            $create
        );

        $this->info("Created Migration: {$file}");
    }

    /**
     * Get the path to the migration directory.
     *
     * @param  string  $package
     * @param  bool  $isTenant
     * @return string
     */
    protected function getMigrationPath($package, $isTenant)
    {
        if ($this->option('path')) {
            return $this->option('path');
        }

        $packagePath = $this->registry->getPackagePath($package);

        if (!$packagePath) {
            throw new \RuntimeException("Could not determine path for package '{$package}'");
        }

        $basePath = $packagePath . '/database/migrations';

        if ($isTenant) {
            return $basePath . '/tenant';
        }

        return $basePath;
    }
}
