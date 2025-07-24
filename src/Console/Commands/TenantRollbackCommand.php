<?php

namespace Ingenius\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Ingenius\Core\Support\MigrationRegistry;
use Stancl\Tenancy\Concerns\DealsWithMigrations;
use Stancl\Tenancy\Concerns\HasATenantsOption;
use Stancl\Tenancy\Contracts\Tenant;

class TenantRollbackCommand extends Command
{
    use HasATenantsOption, DealsWithMigrations, ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ingenius:tenants:rollback
                            {--database= : The database connection to use}
                            {--force : Force the operation to run when in production}
                            {--path= : The path to the migrations files to be executed}
                            {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}
                            {--pretend : Dump the SQL queries that would be run}
                            {--step=1 : The number of migrations to be reverted}
                            {--tenants=* : The tenant(s) to rollback migrations for}
                            {--all : Run for all tenants}
                            {--package= : Rollback migrations for a specific package only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback tenant migrations for all Ingenius packages';

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
            ? $this->registry->tenantForPackage($package)
            : $this->registry->allTenant();

        if (empty($migrations)) {
            $this->info('No tenant migrations found.');
            return 0;
        }

        $this->info('Rolling back tenant migrations for Ingenius packages...');

        if (! $this->confirmToProceed()) {
            return 1;
        }

        $tenants = $this->getTenants();

        if ($tenants->isEmpty()) {
            $this->error('No tenants found.');
            return 1;
        }

        $tenants->each(function (Tenant $tenant) use ($migrations) {
            $this->line("Tenant: {$tenant->getTenantKey()}");

            $tenant->run(function () use ($migrations, $tenant) {
                foreach ($migrations as $migration) {
                    $this->rollbackTenantMigration($migration, $tenant);
                }
            });
        });

        $this->info('All tenant migrations have been rolled back successfully.');

        return 0;
    }

    /**
     * Rollback a tenant migration.
     *
     * @param  array  $migration
     * @param  \Stancl\Tenancy\Contracts\Tenant  $tenant
     * @return void
     */
    protected function rollbackTenantMigration(array $migration, Tenant $tenant)
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
        } else {
            $options['--database'] = config('tenancy.database.tenant_connection_name', 'tenant');
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
