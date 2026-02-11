<?php

namespace Ingenius\Core\Interfaces;

/**
 * Interface for scheduled tasks that can be registered by packages
 *
 * Allows packages to register their own scheduled tasks in a modular way
 * without modifying core scheduling logic.
 */
interface ScheduledTaskInterface
{
    /**
     * Define the schedule for this task
     *
     * Can return Laravel schedule methods like:
     * - 'everyMinute', 'everyFiveMinutes', 'everyTenMinutes', 'everyFifteenMinutes', 'everyThirtyMinutes'
     * - 'hourly', 'daily', 'weekly', 'monthly', 'yearly'
     * - Or a cron expression like '0 0 * * *' (daily at midnight)
     *
     * @return string The schedule expression
     */
    public function schedule(): string;

    /**
     * Execute the scheduled task
     *
     * @return void
     */
    public function handle(): void;

    /**
     * Get a human-readable description of what this task does
     *
     * @return string Task description
     */
    public function description(): string;

    /**
     * Determine if this task should run per-tenant or globally
     *
     * @return bool True if task should run for each tenant, false for global execution
     */
    public function isTenantAware(): bool;

    /**
     * Get a unique identifier for this task
     *
     * @return string Unique task identifier (e.g., 'payforms:cancel-expired-orders')
     */
    public function getIdentifier(): string;
}
