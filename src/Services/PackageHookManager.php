<?php

namespace Ingenius\Core\Services;

/**
 * PackageHookManager - Generic inter-package communication system
 *
 * Allows packages to communicate without tight coupling.
 * Any package can dispatch hooks, any package can register handlers.
 */
class PackageHookManager
{
    /**
     * Registered hooks
     *
     * @var array<string, array>
     */
    protected array $hooks = [];

    /**
     * Register a hook handler
     *
     * @param string $hookName The hook identifier (e.g., 'shipping.cost.calculated')
     * @param callable $handler The handler function that receives ($data, $context)
     * @param int $priority Lower numbers run first
     * @return void
     */
    public function register(string $hookName, callable $handler, int $priority = 50): void
    {
        if (!isset($this->hooks[$hookName])) {
            $this->hooks[$hookName] = [];
        }

        $this->hooks[$hookName][] = [
            'handler' => $handler,
            'priority' => $priority,
        ];

        // Sort by priority (lower numbers first)
        usort($this->hooks[$hookName], fn($a, $b) => $a['priority'] <=> $b['priority']);
    }

    /**
     * Execute a hook and allow registered handlers to modify data
     *
     * @param string $hookName The hook identifier
     * @param mixed $data The data to pass through handlers
     * @param array $context Additional context data (read-only for handlers)
     * @return mixed Modified data after passing through all handlers
     */
    public function execute(string $hookName, mixed $data, array $context = []): mixed
    {
        if (!isset($this->hooks[$hookName])) {
            return $data;
        }

        foreach ($this->hooks[$hookName] as $hook) {
            $data = call_user_func($hook['handler'], $data, $context);
        }

        return $data;
    }

    /**
     * Check if a hook has registered handlers
     *
     * @param string $hookName The hook identifier
     * @return bool
     */
    public function hasHook(string $hookName): bool
    {
        return isset($this->hooks[$hookName]) && !empty($this->hooks[$hookName]);
    }

    /**
     * Get all registered hooks (for debugging)
     *
     * @return array<string, int> Hook names and their handler counts
     */
    public function getRegisteredHooks(): array
    {
        $result = [];

        foreach ($this->hooks as $hookName => $handlers) {
            $result[$hookName] = count($handlers);
        }

        return $result;
    }

    /**
     * Remove all handlers for a specific hook
     *
     * @param string $hookName The hook identifier
     * @return void
     */
    public function clear(string $hookName): void
    {
        unset($this->hooks[$hookName]);
    }

    /**
     * Remove all registered hooks
     *
     * @return void
     */
    public function clearAll(): void
    {
        $this->hooks = [];
    }
}
