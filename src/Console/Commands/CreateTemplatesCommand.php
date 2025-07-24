<?php

namespace Ingenius\Core\Console\Commands;

use Illuminate\Console\Command;
use Ingenius\Core\Models\Template;
use Ingenius\Core\Services\FeatureManager;

class CreateTemplatesCommand extends Command
{
    protected $signature = 'ingenius:create-templates';

    protected $description = 'Create basic templates with default features from installed packages';

    public function handle()
    {
        $this->info('Setting up basic templates...');

        if (!$this->confirm('Would you like to create basic templates with default features?', true)) {
            $this->comment('Skipping basic templates creation.');
            return 0;
        }

        try {
            $featureManager = app(FeatureManager::class);
            $basicFeatures = collect($featureManager->getBasicFeatures());

            if ($basicFeatures->isEmpty()) {
                $this->warn('No basic features found. Skipping template creation.');
                $this->line('');
                $this->line('This might happen if:');
                $this->line('  - No packages with basic features are installed');
                $this->line('  - Service providers are not properly registered');
                $this->line('  - Features are not marked as basic (isBasic() returns false)');
                $this->line('');
                $this->line('Try running: php artisan package:discover');
                return 1;
            }

            $this->info("Found {$basicFeatures->count()} basic features:");
            foreach ($basicFeatures as $identifier => $feature) {
                $this->line("  - {$identifier} ({$feature->getName()}) from {$feature->getPackage()}");
            }
            $this->newLine();

            $templates = [
                [
                    'name' => 'Basic Template',
                    'description' => 'A minimal template with essential features for simple store applications',
                    'identifier' => 'basic',
                    'features' => $basicFeatures->keys()->toArray(),
                ]
            ];

            $createdCount = 0;

            foreach ($templates as $templateData) {
                $existingTemplate = Template::where('identifier', $templateData['identifier'])->first();

                if ($existingTemplate) {
                    $this->line("Template '{$templateData['name']}' already exists. Skipping...");
                    continue;
                }

                Template::create($templateData);
                $this->info("✓ Created template: {$templateData['name']}");
                $createdCount++;
            }

            if ($createdCount > 0) {
                $this->info("Successfully created {$createdCount} basic templates.");
            } else {
                $this->comment('All basic templates already exist.');
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to create basic templates: ' . $e->getMessage());
            $this->warn('You can create templates manually later using the Template model.');
            return 1;
        }
    }
}
