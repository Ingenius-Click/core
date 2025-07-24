<?php

namespace Ingenius\Core\Console\Commands;

use Illuminate\Console\Command;
use Ingenius\Core\Support\PermissionsManager;

class AddAdminRoleToCentralUserCommand extends Command
{
    protected $signature = 'ingenius:core:add-admin-role-to-user 
                            {user : The user ID or email in the central database}';

    protected $description = 'Adds an admin role to an existing user in the central database';

    public function __construct(
        protected PermissionsManager $permissionsManager
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $userIdentifier = $this->argument('user');

        $userClass = config('core.central_user_model');

        if (!$userClass) {
            $this->error("Central user model not configured.");
            return 1;
        }

        if (is_numeric($userIdentifier)) {
            $user = $userClass::find($userIdentifier);
        } else {
            $user = $userClass::where('email', $userIdentifier)->first();
        }

        if (!$user) {
            $this->error("User with identifier {$userIdentifier} not found in central database.");
            return 1;
        }

        $roleModel = config('permission.models.role');

        $adminRole = $roleModel::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
            ['description' => 'Administrator with all permissions']
        );

        $permissionModel = config('permission.models.permission');

        $permissions = $permissionModel::all();

        $adminRole->syncPermissions($permissions);

        $user->assignRole($adminRole);

        $this->info("Successfully assigned admin role to user {$user->name} (ID: {$user->id}) in central database.");
    }
}
