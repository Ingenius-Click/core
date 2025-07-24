<?php

namespace Ingenius\Core\Support;

class PermissionsManager
{
    /**
     * The array of registered central permissions.
     *
     * @var array
     */
    protected array $centralPermissions = [];

    /**
     * The array of registered tenant permissions.
     *
     * @var array
     */
    protected array $tenantPermissions = [];

    /**
     * Register a new permission.
     *
     * @param string $name The permission name
     * @param string $description The permission description
     * @param string $module The module name
     * @param string $context The context (central or tenant)
     * @return void
     */
    public function register(string $name, string $description, string $module, string $context = 'central'): void
    {
        $permission = [
            'name' => $name,
            'description' => $description,
            'module' => $module,
        ];

        if ($context === 'tenant') {
            $this->tenantPermissions[$name] = $permission;
        } else {
            $this->centralPermissions[$name] = $permission;
        }
    }

    /**
     * Register multiple permissions.
     *
     * @param array $permissions Array of permissions
     * @param string $module The module name
     * @param string $context The context (central or tenant)
     * @return void
     */
    public function registerMany(array $permissions, string $module, string $context = 'central'): void
    {
        foreach ($permissions as $name => $description) {
            $this->register($name, $description, $module, $context);
        }
    }

    /**
     * Get all registered permissions.
     *
     * @param string $context The context (central, tenant, or all)
     * @return array
     */
    public function all(string $context = 'all'): array
    {
        if ($context === 'central') {
            return $this->centralPermissions;
        }

        if ($context === 'tenant') {
            return $this->tenantPermissions;
        }

        // Return both central and tenant permissions when context is 'all'
        return array_merge($this->centralPermissions, $this->tenantPermissions);
    }

    /**
     * Get permissions for a specific module.
     *
     * @param string $module
     * @param string $context The context (central, tenant, or all)
     * @return array
     */
    public function forModule(string $module, string $context = 'all'): array
    {
        $permissions = $this->all($context);

        return array_filter($permissions, function ($permission) use ($module) {
            return $permission['module'] === $module;
        });
    }

    /**
     * Set the permissions array.
     *
     * @param array $permissions
     * @param string $context The context (central or tenant)
     * @return void
     */
    public function setPermissions(array $permissions, string $context = 'central'): void
    {
        if ($context === 'tenant') {
            $this->tenantPermissions = $permissions;
        } else {
            $this->centralPermissions = $permissions;
        }
    }

    /**
     * Get all permission names.
     *
     * @param string $context The context (central, tenant, or all)
     * @return array
     */
    public function names(string $context = 'all'): array
    {
        return array_keys($this->all($context));
    }

    /**
     * Get central permissions only.
     *
     * @return array
     */
    public function central(): array
    {
        return $this->centralPermissions;
    }

    /**
     * Get tenant permissions only.
     *
     * @return array
     */
    public function tenant(): array
    {
        return $this->tenantPermissions;
    }
}
