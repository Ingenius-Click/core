<?php

namespace Ingenius\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Ingenius\Core\Support\MigrationRegistry;
use Stancl\Tenancy\Concerns\DealsWithMigrations;
use Stancl\Tenancy\Concerns\HasATenantsOption;
use Stancl\Tenancy\Contracts\Tenant;
use Illuminate\Support\Facades\File;

class TenantMigrateCommand extends Command
{
    use HasATenantsOption, DealsWithMigrations, ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ingenius:tenants:migrate
                            {--database= : The database connection to use}
                            {--force : Force the operation to run when in production}
                            {--path= : The path to the migrations files to be executed}
                            {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}
                            {--pretend : Dump the SQL queries that would be run}
                            {--seed : Indicates if the seed task should be re-run}
                            {--step : Force the migrations to be run so they can be rolled back individually}
                            {--tenants=* : The tenant(s) to run migrations for}
                            {--all : Run for all tenants}
                            {--package= : Run migrations for a specific package only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run tenant migrations for all Ingenius packages';

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
        // Check if the central tenant migrations directory exists
        $centralTenantMigrationsPath = database_path('migrations/tenant');
        if (!File::isDirectory($centralTenantMigrationsPath) || count(File::glob("{$centralTenantMigrationsPath}/*.php")) === 0) {
            $this->info('No tenant migrations found in the central location.');
            $this->info('Run "ingenius:publish:tenant-migrations" to publish tenant migrations from packages.');
            return 0;
        }

        $this->info('Running tenant migrations from central location...');

        if (! $this->confirmToProceed()) {
            return 1;
        }

        $tenants = $this->getTenants();

        if ($tenants->isEmpty()) {
            $this->error('No tenants found.');
            return 1;
        }

        $tenants->each(function (Tenant $tenant) use ($centralTenantMigrationsPath) {
            $this->line("Tenant: {$tenant->getTenantKey()}");

            $tenant->run(function () use ($centralTenantMigrationsPath, $tenant) {
                $this->runTenantMigration($centralTenantMigrationsPath, $tenant);

                if ($this->option('seed')) {
                    $this->call('db:seed', ['--force' => $this->option('force')]);
                }
            });
        });

        $this->info('All tenant migrations have been run successfully.');

        return 0;
    }

    /**
     * Run a tenant migration.
     *
     * @param  string  $path
     * @param  \Stancl\Tenancy\Contracts\Tenant  $tenant
     * @return void
     */
    protected function runTenantMigration(string $path, Tenant $tenant)
    {
        $this->line("<info>Running migrations from central tenant directory</info>");

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
            $options['--step'] = true;
        }

        $this->call('migrate', $options);
    }
}
