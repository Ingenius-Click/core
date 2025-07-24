<?php

namespace Ingenius\Core\Tests\Feature;

use Ingenius\Core\Models\Tenant;
use Ingenius\Core\Tests\TestCase;

class TenantTest extends TestCase
{
    /** @test */
    public function it_can_create_a_tenant()
    {
        $tenant = Tenant::create(['id' => 'test-tenant']);

        $this->assertDatabaseHas('tenants', [
            'id' => 'test-tenant',
        ]);
    }

    /** @test */
    public function it_can_create_a_domain_for_tenant()
    {
        $tenant = Tenant::create(['id' => 'test-tenant']);
        $domain = $tenant->domains()->create(['domain' => 'test.example.com']);

        $this->assertDatabaseHas('domains', [
            'domain' => 'test.example.com',
            'tenant_id' => 'test-tenant',
        ]);
    }
}
