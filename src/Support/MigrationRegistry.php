<?php

namespace Ingenius\Core\Support;

class MigrationRegistry
{
    /**
     * The registered regular migrations.
     *
     * @var array
     */
    protected $migrations = [];

    /**
     * The registered tenant migrations.
     *
     * @var array
     */
    protected $tenantMigrations = [];

    /**
     * The registered package paths.
     *
     * @var array
     */
    protected $packagePaths = [];

    /**
     * Register a regular migration path.
     *
     * @param string $path
     * @param string $package
     * @return void
     */
    public function register(string $path, string $package): void
    {
        if (!in_array(['path' => $path, 'package' => $package], $this->migrations)) {
            $this->migrations[] = ['path' => $path, 'package' => $package];
        }
    }

    /**
     * Register a tenant migration path.
     *
     * @param string $path
     * @param string $package
     * @return void
     */
    public function registerTenant(string $path, string $package): void
    {
        if (!in_array(['path' => $path, 'package' => $package], $this->tenantMigrations)) {
            $this->tenantMigrations[] = ['path' => $path, 'package' => $package];
        }
    }

    /**
     * Register a package path.
     *
     * @param string $package
     * @param string $path
     * @return void
     */
    public function registerPackagePath(string $package, string $path): void
    {
        $this->packagePaths[$package] = $path;
    }

    /**
     * Get all registered regular migration paths.
     *
     * @return array
     */
    public function all(): array
    {
        // Sort migrations to ensure 'core' package migrations are first
        $sortedMigrations = $this->migrations;

        usort($sortedMigrations, function ($a, $b) {
            if ($a['package'] === 'core') {
                return -1;
            }
            if ($b['package'] === 'core') {
                return 1;
            }
            return 0;
        });

        return $sortedMigrations;
    }

    /**
     * Get all registered tenant migration paths.
     *
     * @return array
     */
    public function allTenant(): array
    {
        // Sort migrations to ensure 'core' package migrations are first
        $sortedMigrations = $this->tenantMigrations;

        usort($sortedMigrations, function ($a, $b) {
            if ($a['package'] === 'core') {
                return -1;
            }
            if ($b['package'] === 'core') {
                return 1;
            }
            return 0;
        });

        return $sortedMigrations;
    }

    /**
     * Get all migration paths for a specific package.
     *
     * @param string $package
     * @return array
     */
    public function forPackage(string $package): array
    {
        return array_filter($this->migrations, function ($migration) use ($package) {
            return $migration['package'] === $package;
        });
    }

    /**
     * Get all tenant migration paths for a specific package.
     *
     * @param string $package
     * @return array
     */
    public function tenantForPackage(string $package): array
    {
        return array_filter($this->tenantMigrations, function ($migration) use ($package) {
            return $migration['package'] === $package;
        });
    }

    /**
     * Get the base path for a package.
     *
     * @param string $package
     * @return string|null
     */
    public function getPackagePath(string $package): ?string
    {
        return $this->packagePaths[$package] ?? null;
    }

    /**
     * Get all registered package paths.
     *
     * @return array
     */
    public function getPackagePaths(): array
    {
        return $this->packagePaths;
    }

    /**
     * Check if a package is registered.
     *
     * @param string $package
     * @return bool
     */
    public function hasPackage(string $package): bool
    {
        return isset($this->packagePaths[$package]);
    }
}
