<?php

namespace Ingenius\Core\Console\Commands;

use Illuminate\Console\Command;
use Ingenius\Core\Services\SettingsService;

class ClearSettingsCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'settings:clear-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the settings cache';

    /**
     * Execute the console command.
     */
    public function handle(SettingsService $settingsService)
    {
        $settingsService->clearCache();
        $this->info('Settings cache cleared successfully.');
    }
}
