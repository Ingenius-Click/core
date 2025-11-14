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
     * @param string|null $displayName The display name (optional, will be auto-generated if not provided)
     * @param string|null $group The group (optional, will be auto-generated if not provided)
     * @return void
     */
    public function register(
        string $name,
        string $description,
        string $module,
        string $context = 'central',
        ?string $displayName = null,
        ?string $group = null
    ): void {
        // Auto-generate display_name and group if not provided
        if ($displayName === null || $group === null) {
            $derived = $this->derivePermissionData($name);
            $displayName = $displayName ?? $derived['display_name'];
            $group = $group ?? $derived['group'];
        }

        $permission = [
            'name' => $name,
            'description' => $description,
            'module' => $module,
            'display_name' => $displayName,
            'group' => $group,
        ];

        if ($context === 'tenant') {
            $this->tenantPermissions[$name] = $permission;
        } else {
            $this->centralPermissions[$name] = $permission;
        }
    }

    /**
     * Derive display_name and group from permission name
     *
     * Expected format: "resource.action" or "module:resource.action"
     * Examples:
     * - "products.view" -> group: "Products", display_name: "View Products"
     * - "products.create" -> group: "Products", display_name: "Create Products"
     */
    protected function derivePermissionData(string $permissionName): array
    {
        // Default fallback
        $displayName = ucwords(str_replace(['.', '_', '-'], ' ', $permissionName));
        $group = 'General';

        // Parse permission name (format: "resource.action" or "module:resource.action")
        if (str_contains($permissionName, '.')) {
            $parts = explode('.', $permissionName);

            if (count($parts) >= 2) {
                $resource = $parts[0];
                $action = $parts[1];

                // Handle module prefix (e.g., "shop:products")
                if (str_contains($resource, ':')) {
                    $resourceParts = explode(':', $resource);
                    $resource = end($resourceParts);
                }

                // Generate group (capitalize and singularize/pluralize as needed)
                $group = ucfirst($resource);

                // Generate display name (Action + Resource)
                // e.g., "view" + "products" -> "View Products"
                $actionLabel = ucfirst($action);
                $resourceLabel = ucfirst($resource);
                $displayName = "{$actionLabel} {$resourceLabel}";
            }
        }

        return [
            'display_name' => $displayName,
            'group' => $group,
        ];
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
