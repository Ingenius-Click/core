<?php

namespace Ingenius\Core\Interfaces;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Ingenius\Core\Models\Tenant;

interface TenantInitializer
{
    /**
     * Initialize a new tenant with required data
     *
     * @param Tenant $tenant
     * @param Command $command
     * @return void
     */
    public function initialize(Tenant $tenant, Command $command): void;

    public function initializeViaRequest(Tenant $tenant, Request $request): void;

    public function rules(): array;

    /**
     * Get the priority of this initializer
     * Higher priority initializers run first
     *
     * @return int
     */
    public function getPriority(): int;

    /**
     * Get the name of this initializer
     * Used for display in the command
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the package name of this initializer
     * Used for filtering initializers by package
     *
     * @return string
     */
    public function getPackageName(): string;
}
