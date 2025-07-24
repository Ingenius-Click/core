<?php

namespace Ingenius\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class RegisterSettingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'settings:register';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register all settings classes and their default values';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $settingsClasses = Config::get('settings.settings_classes', []);

        if (empty($settingsClasses)) {
            $this->info('No settings classes found to register.');
            return;
        }

        $this->info('Registering settings classes...');
        $count = 0;

        foreach ($settingsClasses as $settingsClass) {
            if (!class_exists($settingsClass)) {
                $this->warn("Class {$settingsClass} does not exist. Skipping.");
                continue;
            }

            $instance = new $settingsClass();
            $instance->save();
            $count++;

            $this->info("Registered {$settingsClass}");
        }

        $this->info("Successfully registered {$count} settings classes.");
    }
}
