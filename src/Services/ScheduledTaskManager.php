<?php

namespace Ingenius\Core\Services;

use Ingenius\Core\Interfaces\ScheduledTaskInterface;
use InvalidArgumentException;

/**
 * ScheduledTaskManager - Manages scheduled tasks across packages
 *
 * Allows packages to register scheduled tasks in a modular way.
 * Each package can register tasks via their service providers.
 */
class ScheduledTaskManager
{
    /**
     * Registered scheduled tasks
     *
     * @var array<string, ScheduledTaskInterface>
     */
    protected array $tasks = [];

    /**
     * Register a scheduled task
     *
     * @param ScheduledTaskInterface $task The task to register
     * @return void
     * @throws InvalidArgumentException If task with same identifier already registered
     */
    public function register(ScheduledTaskInterface $task): void
    {
        $identifier = $task->getIdentifier();

        if (isset($this->tasks[$identifier])) {
            throw new InvalidArgumentException(
                "Scheduled task with identifier [{$identifier}] is already registered."
            );
        }

        $this->tasks[$identifier] = $task;
    }

    /**
     * Get all registered scheduled tasks
     *
     * @return array<string, ScheduledTaskInterface>
     */
    public function getTasks(): array
    {
        return $this->tasks;
    }

    /**
     * Get a specific task by identifier
     *
     * @param string $identifier The task identifier
     * @return ScheduledTaskInterface|null
     */
    public function getTask(string $identifier): ?ScheduledTaskInterface
    {
        return $this->tasks[$identifier] ?? null;
    }

    /**
     * Check if a task is registered
     *
     * @param string $identifier The task identifier
     * @return bool
     */
    public function hasTask(string $identifier): bool
    {
        return isset($this->tasks[$identifier]);
    }

    /**
     * Get all tenant-aware tasks
     *
     * @return array<string, ScheduledTaskInterface>
     */
    public function getTenantAwareTasks(): array
    {
        return array_filter($this->tasks, fn($task) => $task->isTenantAware());
    }

    /**
     * Get all global tasks (non-tenant-aware)
     *
     * @return array<string, ScheduledTaskInterface>
     */
    public function getGlobalTasks(): array
    {
        return array_filter($this->tasks, fn($task) => !$task->isTenantAware());
    }

    /**
     * Get task count
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->tasks);
    }

    /**
     * Remove a task by identifier
     *
     * @param string $identifier The task identifier
     * @return void
     */
    public function remove(string $identifier): void
    {
        unset($this->tasks[$identifier]);
    }

    /**
     * Clear all registered tasks
     *
     * @return void
     */
    public function clearAll(): void
    {
        $this->tasks = [];
    }
}
