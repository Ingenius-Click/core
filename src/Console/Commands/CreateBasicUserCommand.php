<?php

namespace Ingenius\Core\Console\Commands;

use Illuminate\Console\Command;

class CreateBasicUserCommand extends Command
{
    protected $signature = 'ingenius:create-user';

    protected $description = 'Create a basic user for the application';

    public function handle()
    {
        $this->info('Creating basic user...');

        // First, sync central permissions
        $this->info('Syncing central permissions...');
        $this->call('ingenius:core:sync-central-permissions');

        if (!$this->confirm('Would you like to create a basic user?', true)) {
            $this->comment('Skipping basic user creation.');
            return 0;
        }

        try {
            $name = $this->ask('Enter the name for the basic user');
            $email = $this->ask('Enter the email address for the basic user');
            $password = $this->secret('Enter the password for the basic user');

            // Validate inputs
            if (empty($name) || empty($email) || empty($password)) {
                $this->error('All fields are required.');
                return 1;
            }

            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->error('Please enter a valid email address.');
                return 1;
            }

            $userClass = config('core.central_user_model');

            if (!class_exists($userClass)) {
                $this->error("User model class '{$userClass}' not found. Please check your core.central_user_model configuration.");
                return 1;
            }

            // Check if user already exists
            if ($userClass::where('email', $email)->exists()) {
                $this->error("A user with email '{$email}' already exists.");
                return 1;
            }

            $user = $userClass::create([
                'name' => $name,
                'email' => $email,
                'password' => bcrypt($password),
            ]);

            // Create admin role if it doesn't exist and assign it to the user
            $this->info('Creating admin role and assigning to user...');

            $roleModel = config('permission.models.role');

            $adminRole = $roleModel::firstOrCreate(
                ['name' => 'admin', 'guard_name' => 'web'],
                ['description' => 'Administrator with all permissions']
            );

            $permissionModel = config('permission.models.permission');
            $permissions = $permissionModel::all();
            $adminRole->syncPermissions($permissions);

            $user->assignRole($adminRole);

            $this->info('✓ Basic user created successfully.');
            $this->info('✓ Admin role assigned successfully.');
            $this->line('User Details:');
            $this->line("  - ID: {$user->id}");
            $this->line("  - Name: {$name}");
            $this->line("  - Email: {$email}");
            $this->line("  - Role: admin");

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to create basic user: ' . $e->getMessage());
            return 1;
        }
    }
}
