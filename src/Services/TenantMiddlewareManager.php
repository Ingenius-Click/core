<?php

namespace Ingenius\Core\Services;

/**
 * TenantMiddlewareManager - Allows packages to register middleware for tenant routes
 *
 * Provides a centralized way for packages to register middleware that should be applied
 * to all tenant API routes without modifying bootstrap/app.php or route files.
 */
class TenantMiddlewareManager
{
    /**
     * Registered middleware classes
     *
     * @var array<int, string>
     */
    protected array $middleware = [];

    /**
     * Register a middleware class to be applied to tenant routes
     *
     * @param string $middlewareClass The fully qualified middleware class name
     * @param int $priority Lower numbers run first (default: 50)
     * @return void
     */
    public function register(string $middlewareClass, int $priority = 50): void
    {
        $this->middleware[] = [
            'class' => $middlewareClass,
            'priority' => $priority,
        ];

        // Sort by priority (lower numbers first)
        usort($this->middleware, fn($a, $b) => $a['priority'] <=> $b['priority']);
    }

    /**
     * Get all registered middleware classes in priority order
     *
     * @return array<string>
     */
    public function getMiddleware(): array
    {
        return array_map(fn($m) => $m['class'], $this->middleware);
    }

    /**
     * Check if any middleware is registered
     *
     * @return bool
     */
    public function hasMiddleware(): bool
    {
        return !empty($this->middleware);
    }

    /**
     * Clear all registered middleware
     *
     * @return void
     */
    public function clear(): void
    {
        $this->middleware = [];
    }
}
