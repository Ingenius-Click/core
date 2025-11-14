<?php

namespace Ingenius\Core\Console\Commands;

use Illuminate\Console\Command;
use Ingenius\Core\Support\PermissionsManager;

class SyncCentralPermissionsCommand extends Command
{
    protected $signature = 'ingenius:core:sync-central-permissions';

    protected $description = 'Synchronize permissions from PermissionsManager to central database';

    public function __construct(
        protected PermissionsManager $permissionsManager
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $permissions = $this->permissionsManager->central();

        $count = count($permissions);

        $this->info("Synchronizing {$count} permissions...");

        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();

        $permissionModel = config('permission.models.permission');

        foreach ($permissions as $permissionName => $permissionData) {

            $permissionModel::updateOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ], [
                'description' => $permissionData['description'],
                'display_name' => $permissionData['display_name'] ?? null,
                'group' => $permissionData['group'] ?? null,
            ]);

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        $this->info('Permissions synchronized successfully');
    }
}
