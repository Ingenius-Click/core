<?php

namespace Ingenius\Core\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class UserModelHelper
{
    /**
     * Get the configured central user model class.
     *
     * @return string
     */
    public static function getCentralUserModel(): string
    {
        return config('core.central_user_model', self::getDefaultUserModel());
    }

    /**
     * Get the default user model based on what exists.
     *
     * @return string
     */
    public static function getDefaultUserModel(): string
    {
        // Check if App\Models\User exists
        if (class_exists('App\\Models\\User')) {
            return 'App\\Models\\User';
        }

        // Fall back to core package User model
        return 'Ingenius\\Core\\Models\\User';
    }

    /**
     * Check if the Laravel default User model exists.
     *
     * @return bool
     */
    public static function laravelUserModelExists(): bool
    {
        return class_exists('App\\Models\\User') && File::exists(app_path('Models/User.php'));
    }

    /**
     * Check if the users table exists in the database.
     *
     * @return bool
     */
    public static function usersTableExists(): bool
    {
        try {
            return Schema::hasTable('users');
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if the default Laravel user migration exists.
     *
     * @return bool
     */
    public static function laravelUserMigrationExists(): bool
    {
        $migrationPath = database_path('migrations');

        if (!File::isDirectory($migrationPath)) {
            return false;
        }

        $migrationFiles = File::glob("{$migrationPath}/*_create_users_table.php");

        return !empty($migrationFiles);
    }

    /**
     * Check if core user migration is published.
     *
     * @return bool
     */
    public static function coreUserMigrationPublished(): bool
    {
        $migrationPath = database_path('migrations');

        if (!File::isDirectory($migrationPath)) {
            return false;
        }

        $migrationFiles = File::glob("{$migrationPath}/*_create_central_users_table.php");

        return !empty($migrationFiles);
    }

    /**
     * Check if core User model is published.
     *
     * @return bool
     */
    public static function coreUserModelPublished(): bool
    {
        return File::exists(app_path('Models/CoreUser.php'));
    }

    /**
     * Determine if we should use the core User model.
     *
     * @return bool
     */
    public static function shouldUseCoreUserModel(): bool
    {
        // If explicitly configured to use core model
        $configuredModel = config('core.central_user_model');
        if ($configuredModel === 'Ingenius\\Core\\Models\\User') {
            return true;
        }

        // If Laravel User model doesn't exist
        if (!self::laravelUserModelExists()) {
            return true;
        }

        // If publishing is enabled
        if (config('core.publish_user_model', false)) {
            return true;
        }

        return false;
    }

    /**
     * Determine if we need to create user migration.
     *
     * @return bool
     */
    public static function needsUserMigration(): bool
    {
        // If users table already exists, no migration needed
        if (self::usersTableExists()) {
            return false;
        }

        // If Laravel migration exists, no core migration needed
        if (self::laravelUserMigrationExists()) {
            return false;
        }

        // If using core model or publishing is enabled
        return self::shouldUseCoreUserModel() || config('core.publish_user_migration', false);
    }

    /**
     * Get the current user model status.
     *
     * @return array
     */
    public static function getUserModelStatus(): array
    {
        return [
            'configured_model' => self::getCentralUserModel(),
            'laravel_model_exists' => self::laravelUserModelExists(),
            'users_table_exists' => self::usersTableExists(),
            'laravel_migration_exists' => self::laravelUserMigrationExists(),
            'core_migration_published' => self::coreUserMigrationPublished(),
            'core_model_published' => self::coreUserModelPublished(),
            'should_use_core_model' => self::shouldUseCoreUserModel(),
            'needs_migration' => self::needsUserMigration(),
        ];
    }

    /**
     * Create an instance of the configured central user model.
     *
     * @return \Illuminate\Foundation\Auth\User
     */
    public static function createUserInstance()
    {
        $modelClass = self::getCentralUserModel();

        if (!class_exists($modelClass)) {
            throw new \Exception("User model class '{$modelClass}' does not exist.");
        }

        return new $modelClass;
    }

    /**
     * Get the table name for the user model.
     *
     * @return string
     */
    public static function getUserTableName(): string
    {
        try {
            $userInstance = self::createUserInstance();
            return $userInstance->getTable();
        } catch (\Exception $e) {
            return 'users'; // Default fallback
        }
    }
}
