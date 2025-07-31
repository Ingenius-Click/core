<?php

namespace Ingenius\Core\Initializers;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Ingenius\Core\Interfaces\TenantInitializer;
use Ingenius\Core\Models\Tenant;
use Ingenius\Core\Settings\CustomizeSettings;

class CustomizeInitializer implements TenantInitializer
{
    /**
     * Create a new initializer instance.
     */
    public function __construct(
        protected CustomizeSettings $customizeSettings
    ) {}

    /**
     * Initialize a new tenant with customization settings
     *
     * @param Tenant $tenant
     * @param Command $command
     * @return void
     */
    public function initialize(Tenant $tenant, Command $command): void
    {
        $command->info('Setting up store customization...');

        // Set default store name from tenant name
        $this->customizeSettings->store_name = $tenant->getName();

        // Ask if user wants to upload a logo
        if ($command->confirm('Would you like to upload a store logo?', false)) {
            $logoPath = $command->ask('Enter the path to your logo file');

            if ($logoPath && file_exists($logoPath)) {
                // Store the logo file
                $storedPath = $this->storeLogoFile($logoPath);
                $this->customizeSettings->store_logo = $storedPath;
                $command->info("Logo uploaded successfully: {$storedPath}");
            } else {
                $command->warn('Logo file not found or invalid path provided.');
            }
        }

        $this->customizeSettings->save();
        $command->info('Store customization setup completed.');
    }

    /**
     * Initialize via web request
     *
     * @param Tenant $tenant
     * @param Request $request
     * @return void
     */
    public function initializeViaRequest(Tenant $tenant, Request $request): void
    {
        // Set store name from tenant name
        $this->customizeSettings->store_name = $tenant->getName();

        // Handle logo upload if provided
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $this->customizeSettings->store_logo = $logoPath;
        }

        $this->customizeSettings->save();
    }

    /**
     * Get validation rules for this initializer
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:512',
        ];
    }

    /**
     * Get the priority of this initializer
     * Higher priority initializers run first
     *
     * @return int
     */
    public function getPriority(): int
    {
        // Run after auth (100) but before most other packages
        return 85;
    }

    /**
     * Get the name of this initializer
     *
     * @return string
     */
    public function getName(): string
    {
        return 'Store Customization Setup';
    }

    /**
     * Get the package name of this initializer
     *
     * @return string
     */
    public function getPackageName(): string
    {
        return 'core';
    }

    /**
     * Store logo file from file path
     *
     * @param string $filePath
     * @return string
     */
    protected function storeLogoFile(string $filePath): string
    {
        $fileName = basename($filePath);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $storedName = 'logo_' . time() . '.' . $extension;

        // Copy file to storage
        $storagePath = storage_path('app/public/logos/' . $storedName);

        // Ensure directory exists
        if (!file_exists(dirname($storagePath))) {
            mkdir(dirname($storagePath), 0755, true);
        }

        copy($filePath, $storagePath);

        return 'logos/' . $storedName;
    }
}
