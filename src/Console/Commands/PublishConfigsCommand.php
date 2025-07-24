<?php

namespace Ingenius\Core\Console\Commands;

use Illuminate\Console\Command;
use Ingenius\Core\Support\ConfigRegistry;
use Illuminate\Support\Facades\File;

class PublishConfigsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ingenius:publish:configs
                            {--package= : The package to publish configs for}
                            {--force : Force overwrite of existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish configuration files for Ingenius packages';

    /**
     * The config registry instance.
     *
     * @var \Ingenius\Core\Support\ConfigRegistry
     */
    protected $registry;

    /**
     * Create a new command instance.
     *
     * @param  \Ingenius\Core\Support\ConfigRegistry  $registry
     * @return void
     */
    public function __construct(ConfigRegistry $registry)
    {
        parent::__construct();

        $this->registry = $registry;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $package = $this->option('package');
        $force = $this->option('force');

        $configs = $package
            ? $this->registry->forPackage($package)
            : $this->registry->all();

        if (empty($configs)) {
            $this->info('No configurations found.');
            return 0;
        }

        $this->info('Publishing configurations for Ingenius packages...');

        foreach ($configs as $config) {
            $this->publishConfig($config, $force);
        }

        $this->info('All configurations have been published successfully.');

        return 0;
    }

    /**
     * Publish a configuration file.
     *
     * @param  array  $config
     * @param  bool  $force
     * @return void
     */
    protected function publishConfig(array $config, bool $force)
    {
        $path = $config['path'];
        $key = $config['key'];
        $package = $config['package'];

        $this->line("<info>Publishing configuration for package:</info> {$package}");

        // Create the target directory if it doesn't exist
        $targetDir = config_path(dirname($key));
        if (!is_dir($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        $targetPath = config_path($key . '.php');

        // Check if the file already exists and force is not enabled
        if (file_exists($targetPath) && !$force) {
            $this->line("<comment>File already exists:</comment> {$targetPath}");
            if (!$this->confirm('Do you want to overwrite it?')) {
                $this->line("<comment>Skipping...</comment>");
                return;
            }
        }

        // Copy the file
        File::copy($path, $targetPath);
        $this->line("<info>Published:</info> {$targetPath}");
    }
}
