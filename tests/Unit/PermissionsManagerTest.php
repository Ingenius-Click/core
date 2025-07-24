<?php

namespace Ingenius\Core\Tests\Unit;

use Ingenius\Core\Support\PermissionsManager;
use Ingenius\Core\Tests\TestCase;

class PermissionsManagerTest extends TestCase
{
    /** @test */
    public function it_can_register_permissions()
    {
        $manager = new PermissionsManager();

        $manager->register('test.permission', 'Test Permission', 'Test', 'central');

        $permissions = $manager->all();

        $this->assertCount(1, $permissions);
        $this->assertEquals('test.permission', array_key_first($permissions));
    }

    /** @test */
    public function it_can_register_multiple_permissions()
    {
        $manager = new PermissionsManager();

        $manager->registerMany([
            'test.permission1' => 'Test Permission 1',
            'test.permission2' => 'Test Permission 2',
        ], 'Test', 'central');

        $permissions = $manager->all();

        $this->assertCount(2, $permissions);
    }

    /** @test */
    public function it_can_separate_central_and_tenant_permissions()
    {
        $manager = new PermissionsManager();

        $manager->register('central.permission', 'Central Permission', 'Test', 'central');
        $manager->register('tenant.permission', 'Tenant Permission', 'Test', 'tenant');

        $this->assertCount(1, $manager->central());
        $this->assertCount(1, $manager->tenant());
    }
}
