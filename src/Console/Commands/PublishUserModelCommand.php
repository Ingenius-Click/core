<?php

namespace Ingenius\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Ingenius\Core\Helpers\UserModelHelper;

class PublishUserModelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ingenius:publish:user-model
                            {--model : Publish only the User model}
                            {--migration : Publish only the migration}
                            {--force : Force overwrite existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish the core User model and/or migration for central authentication';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $publishModel = $this->option('model') || (!$this->option('migration'));
        $publishMigration = $this->option('migration') || (!$this->option('model'));
        $force = $this->option('force');

        $this->info('Publishing Core User Model and Migration...');

        // Show current status
        $this->showCurrentStatus();

        $published = false;

        if ($publishModel) {
            $published = $this->publishUserModel($force) || $published;
        }

        if ($publishMigration) {
            $published = $this->publishUserMigration($force) || $published;
        }

        if ($published) {
            $this->info('Core User model and migration published successfully!');
            $this->line('');
            $this->line('Next steps:');
            $this->line('1. Review the published files and customize as needed');
            $this->line('2. Update your .env file with CENTRAL_USER_MODEL if you want to use a custom model');
            $this->line('3. Run "php artisan migrate" to create the users table');
        } else {
            $this->info('No files were published (use --force to overwrite existing files).');
        }

        return 0;
    }

    /**
     * Show the current user model status.
     *
     * @return void
     */
    protected function showCurrentStatus(): void
    {
        $status = UserModelHelper::getUserModelStatus();

        $this->line('Current User Model Status:');
        $this->line('  Configured Model: ' . $status['configured_model']);
        $this->line('  Laravel User Model Exists: ' . ($status['laravel_model_exists'] ? 'Yes' : 'No'));
        $this->line('  Users Table Exists: ' . ($status['users_table_exists'] ? 'Yes' : 'No'));
        $this->line('  Laravel Migration Exists: ' . ($status['laravel_migration_exists'] ? 'Yes' : 'No'));
        $this->line('  Core Migration Published: ' . ($status['core_migration_published'] ? 'Yes' : 'No'));
        $this->line('  Core Model Published: ' . ($status['core_model_published'] ? 'Yes' : 'No'));
        $this->line('');
    }

    /**
     * Publish the User model.
     *
     * @param bool $force
     * @return bool
     */
    protected function publishUserModel(bool $force): bool
    {
        $sourcePath = __DIR__ . '/../../../src/Models/User.php';
        $targetPath = app_path('Models/CoreUser.php');

        // Check if target already exists
        if (File::exists($targetPath) && !$force) {
            $this->line('<comment>User model already published:</comment> ' . $targetPath);
            if (!$this->confirm('Do you want to overwrite it?')) {
                $this->line('<comment>Skipping User model...</comment>');
                return false;
            }
        }

        // Ensure the Models directory exists
        $targetDir = dirname($targetPath);
        if (!File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        // Read the source file and modify the namespace and class name
        $content = File::get($sourcePath);

        // Replace namespace and class name for the published version
        $content = str_replace(
            'namespace Ingenius\Core\Models;',
            'namespace App\Models;',
            $content
        );

        $content = str_replace(
            'class User extends Authenticatable',
            'class CoreUser extends Authenticatable',
            $content
        );

        // Write the modified content
        File::put($targetPath, $content);

        $this->line('<info>Published User model:</info> ' . $targetPath);

        return true;
    }

    /**
     * Publish the User migration.
     *
     * @param bool $force
     * @return bool
     */
    protected function publishUserMigration(bool $force): bool
    {
        $sourcePath = __DIR__ . '/../../../database/migrations/2024_01_01_000001_create_central_users_table.php';
        $timestamp = date('Y_m_d_His');
        $targetPath = database_path("migrations/{$timestamp}_create_central_users_table.php");

        // Check if migration already exists
        $existingMigrations = File::glob(database_path('migrations/*_create_central_users_table.php'));
        if (!empty($existingMigrations) && !$force) {
            $this->line('<comment>Central users migration already published:</comment> ' . $existingMigrations[0]);
            if (!$this->confirm('Do you want to create a new migration?')) {
                $this->line('<comment>Skipping migration...</comment>');
                return false;
            }
        }

        // Ensure the migrations directory exists
        $targetDir = dirname($targetPath);
        if (!File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        // Copy the migration
        File::copy($sourcePath, $targetPath);

        $this->line('<info>Published migration:</info> ' . $targetPath);

        return true;
    }
}
