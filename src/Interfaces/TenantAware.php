<?php

namespace Ingenius\Core\Interfaces;

use Ingenius\Core\Models\Tenant;

interface TenantAware
{
    /**
     * Set the tenant for this instance.
     *
     * @param Tenant $tenant
     * @return void
     */
    public function setTenant(Tenant $tenant): void;

    /**
     * Get the tenant for this instance.
     *
     * @return Tenant|null
     */
    public function getTenant(): ?Tenant;
}
