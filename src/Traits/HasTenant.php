<?php

namespace Ingenius\Core\Traits;

use Ingenius\Core\Models\Tenant;

trait HasTenant
{
    /**
     * The tenant instance.
     *
     * @var Tenant|null
     */
    protected ?Tenant $tenant = null;

    /**
     * Set the tenant for this instance.
     *
     * @param Tenant $tenant
     * @return void
     */
    public function setTenant(Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    /**
     * Get the tenant for this instance.
     *
     * @return Tenant|null
     */
    public function getTenant(): ?Tenant
    {
        return $this->tenant;
    }
}
