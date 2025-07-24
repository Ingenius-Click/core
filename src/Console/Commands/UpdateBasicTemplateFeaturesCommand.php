<?php

namespace Ingenius\Core\Console\Commands;

use Illuminate\Console\Command;
use Ingenius\Core\Models\Template;
use Ingenius\Core\Services\FeatureManager;

class UpdateBasicTemplateFeaturesCommand extends Command
{
    protected $signature = 'ingenius:template:update-basic-features';

    protected $description = 'Update the basic template with all current basic features';

    public function handle()
    {
        $this->info('Updating basic template features...');

        try {
            // Find the basic template
            $basicTemplate = Template::where('identifier', 'basic')->first();

            if (!$basicTemplate) {
                $this->error('Basic template not found. Please run "php artisan ingenius:install" first to create the basic template.');
                return 1;
            }

            // Get all basic features from FeatureManager
            $featureManager = app(FeatureManager::class);
            $basicFeatures = collect($featureManager->getBasicFeatures());

            if ($basicFeatures->isEmpty()) {
                $this->warn('No basic features found. The basic template will have no features.');
                $newFeatures = [];
            } else {
                $newFeatures = $basicFeatures->keys()->toArray();
            }

            // Get current features for comparison
            $currentFeatures = $basicTemplate->features ?? [];

            // Show current state
            $this->line('Current basic template features:');
            if (empty($currentFeatures)) {
                $this->line('  - None');
            } else {
                foreach ($currentFeatures as $feature) {
                    $this->line("  - {$feature}");
                }
            }

            $this->line('');
            $this->line('New basic features found:');
            if (empty($newFeatures)) {
                $this->line('  - None');
            } else {
                foreach ($newFeatures as $feature) {
                    $this->line("  - {$feature}");
                }
            }

            // Check if update is needed
            sort($currentFeatures);
            sort($newFeatures);

            if ($currentFeatures === $newFeatures) {
                $this->info('Basic template features are already up to date. No changes needed.');
                return 0;
            }

            // Show what will change
            $addedFeatures = array_diff($newFeatures, $currentFeatures);
            $removedFeatures = array_diff($currentFeatures, $newFeatures);

            if (!empty($addedFeatures)) {
                $this->line('');
                $this->line('Features to be added:');
                foreach ($addedFeatures as $feature) {
                    $this->line("  + {$feature}");
                }
            }

            if (!empty($removedFeatures)) {
                $this->line('');
                $this->line('Features to be removed:');
                foreach ($removedFeatures as $feature) {
                    $this->line("  - {$feature}");
                }
            }

            // Confirm update
            if (!$this->confirm('Do you want to update the basic template features?', true)) {
                $this->comment('Update cancelled.');
                return 0;
            }

            // Update the template
            $basicTemplate->features = $newFeatures;
            $basicTemplate->save();

            $this->info('✓ Basic template features updated successfully.');
            $this->line("Updated template: {$basicTemplate->name} (ID: {$basicTemplate->id})");
            $this->line("Total features: " . count($newFeatures));
        } catch (\Exception $e) {
            $this->error('Failed to update basic template features: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
