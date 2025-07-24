<?php

namespace Ingenius\Core\Console\Commands;

use Illuminate\Console\Command;
use Ingenius\Core\Support\MigrationRegistry;

class RollbackCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ingenius:rollback 
                            {--database= : The database connection to use}
                            {--force : Force the operation to run when in production}
                            {--path= : The path to the migrations files to be executed}
                            {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}
                            {--pretend : Dump the SQL queries that would be run}
                            {--step=1 : The number of migrations to be reverted}
                            {--package= : Rollback migrations for a specific package only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback migrations for all Ingenius packages';

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

        $migrations = $package
            ? $this->registry->forPackage($package)
            : $this->registry->all();

        if (empty($migrations)) {
            $this->info('No migrations found.');
            return 0;
        }

        $this->info('Rolling back migrations for Ingenius packages...');

        foreach ($migrations as $migration) {
            $this->rollbackMigration($migration);
        }

        $this->info('All migrations have been rolled back successfully.');

        return 0;
    }

    /**
     * Rollback a migration.
     *
     * @param  array  $migration
     * @return void
     */
    protected function rollbackMigration(array $migration)
    {
        $path = $migration['path'];
        $package = $migration['package'];

        $this->line("<info>Rolling back migrations for package:</info> {$package}");

        $options = [
            '--path' => $path,
            '--realpath' => true,
        ];

        if ($this->option('database')) {
            $options['--database'] = $this->option('database');
        }

        if ($this->option('force')) {
            $options['--force'] = true;
        }

        if ($this->option('pretend')) {
            $options['--pretend'] = true;
        }

        if ($this->option('step')) {
            $options['--step'] = $this->option('step');
        }

        $this->call('migrate:rollback', $options);
    }
}
