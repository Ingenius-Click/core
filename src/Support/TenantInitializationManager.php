<?php

namespace Ingenius\Core\Support;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Ingenius\Core\Interfaces\TenantInitializer;
use Ingenius\Core\Models\Tenant;
use Stancl\Tenancy\Tenancy;

class TenantInitializationManager
{
    /**
     * The registered tenant initializers.
     *
     * @var Collection
     */
    protected Collection $initializers;

    /**
     * The tenancy instance.
     *
     * @var Tenancy
     */
    protected Tenancy $tenancy;

    /**
     * Create a new tenant initialization manager instance.
     *
     * @param Tenancy $tenancy
     */
    public function __construct(Tenancy $tenancy)
    {
        $this->initializers = collect();
        $this->tenancy = $tenancy;
    }

    /**
     * Register a tenant initializer.
     *
     * @param TenantInitializer $initializer
     * @return void
     */
    public function register(TenantInitializer $initializer): void
    {
        $this->initializers->push($initializer);
    }

    /**
     * Initialize a tenant with all registered initializers.
     *
     * @param Tenant $tenant
     * @param Command $command
     * @param array $selectedInitializers Optional array of initializer class names to run
     * @return void
     */
    public function initializeTenant(Tenant $tenant, Command $command, array $selectedInitializers = []): void
    {
        // Sort initializers by priority (highest first)
        $sortedInitializers = $this->initializers->sortByDesc(function (TenantInitializer $initializer) {
            return $initializer->getPriority();
        });

        // Filter initializers if a selection was provided
        if (!empty($selectedInitializers)) {
            $sortedInitializers = $sortedInitializers->filter(function (TenantInitializer $initializer) use ($selectedInitializers) {
                return in_array(get_class($initializer), $selectedInitializers);
            });
        }

        // Initialize tenant context
        $this->tenancy->initialize($tenant);

        try {
            // Run each initializer
            foreach ($sortedInitializers as $initializer) {
                $command->info("Running initializer: " . $initializer->getName());
                $initializer->initialize($tenant, $command);
            }
        } finally {
            // Always end tenancy context
            $this->tenancy->end();
        }
    }

    public function initializeTenantViaRequest(Tenant $tenant, Request $request): void
    {
        // Sort initializers by priority (highest first)
        $sortedInitializers = $this->initializers->sortByDesc(function (TenantInitializer $initializer) {
            return $initializer->getPriority();
        });

        $this->tenancy->initialize($tenant);

        foreach ($sortedInitializers as $initializer) {
            $initializer->initializeViaRequest($tenant, $request);
        }

        $this->tenancy->end();
    }

    public function rules(): array
    {
        $rules = $this->initializers->map(function (TenantInitializer $initializer) {
            return $initializer->rules();
        })->flatMap(function ($rules) {
            return $rules;
        })->toArray();

        return $rules;
    }

    /**
     * Get all registered initializers.
     *
     * @return Collection
     */
    public function getInitializers(): Collection
    {
        return $this->initializers;
    }

    /**
     * Initialize a tenant with initializers from a specific package.
     *
     * @param Tenant $tenant
     * @param Command $command
     * @param string $packageName
     * @return void
     */
    public function initializeTenantByPackage(Tenant $tenant, Command $command, string $packageName): void
    {
        $packageInitializers = $this->getInitializersByPackage($packageName);

        if ($packageInitializers->isEmpty()) {
            $command->warn("No initializers found for package: {$packageName}");
            return;
        }

        // Sort initializers by priority (highest first)
        $sortedInitializers = $packageInitializers->sortByDesc(function (TenantInitializer $initializer) {
            return $initializer->getPriority();
        });

        // Initialize tenant context
        $this->tenancy->initialize($tenant);

        try {
            // Run each initializer
            foreach ($sortedInitializers as $initializer) {
                $command->info("Running initializer: " . $initializer->getName());
                $initializer->initialize($tenant, $command);
            }
        } finally {
            // Always end tenancy context
            $this->tenancy->end();
        }
    }

    /**
     * Get initializers for a specific package.
     *
     * @param string $packageName
     * @return Collection
     */
    public function getInitializersByPackage(string $packageName): Collection
    {
        return $this->initializers->filter(function (TenantInitializer $initializer) use ($packageName) {
            return $initializer->getPackageName() === $packageName;
        });
    }

    /**
     * Get all available package names.
     *
     * @return array
     */
    public function getAvailablePackages(): array
    {
        return $this->initializers->map(function (TenantInitializer $initializer) {
            return $initializer->getPackageName();
        })->unique()->sort()->values()->toArray();
    }
}
