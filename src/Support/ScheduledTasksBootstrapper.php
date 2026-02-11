<?php

namespace Ingenius\Core\Support;

use Illuminate\Console\Scheduling\Schedule;
use Ingenius\Core\Services\ScheduledTaskManager;
use Log;

/**
 * Bootstrapper for registering all package scheduled tasks
 *
 * This class provides a simple way to register all scheduled tasks
 * from packages into the application's schedule.
 */
class ScheduledTasksBootstrapper
{
    /**
     * Register all package scheduled tasks with Laravel's scheduler
     *
     * Call this from your routes/console.php file:
     * \Ingenius\Core\Support\ScheduledTasksBootstrapper::register();
     *
     * @return void
     */
    public static function register(): void
    {
        $schedule = app(Schedule::class);
        $taskManager = app(ScheduledTaskManager::class);

        Log::info('Registering scheduled tasks from packages', [
            'task_count' => count($taskManager->getTasks()),
        ]);

        foreach ($taskManager->getTasks() as $task) {
            $scheduleMethod = $task->schedule();

            if ($task->isTenantAware()) {
                // For tenant-aware tasks, run across all tenants
                $scheduledTask = $schedule->call(function () use ($task) {
                    tenancy()->runForMultiple(null, function () use ($task) {
                        $task->handle();
                    });
                });
            } else {
                // For global tasks, run once
                $scheduledTask = $schedule->call(function () use ($task) {
                    $task->handle();
                });
            }

            // Apply the schedule method dynamically
            self::applyScheduleMethod($scheduledTask, $scheduleMethod);

            // Set description for better visibility in schedule:list
            $scheduledTask->description($task->description());

            // Set name for identification
            $scheduledTask->name($task->getIdentifier());
        }
    }

    /**
     * Apply the schedule method to the scheduled task
     *
     * @param \Illuminate\Console\Scheduling\Event $scheduledTask
     * @param string $scheduleMethod
     * @return void
     */
    protected static function applyScheduleMethod($scheduledTask, string $scheduleMethod): void
    {
        // Check if it's a cron expression (contains spaces) or a named method
        if (str_contains($scheduleMethod, ' ')) {
            // It's a cron expression
            $scheduledTask->cron($scheduleMethod);
        } else {
            // It's a named method like 'hourly', 'daily', etc.
            if (method_exists($scheduledTask, $scheduleMethod)) {
                $scheduledTask->$scheduleMethod();
            } else {
                throw new \InvalidArgumentException(
                    "Invalid schedule method: {$scheduleMethod}. " .
                    "Use a valid Laravel schedule method or a cron expression."
                );
            }
        }
    }
}
