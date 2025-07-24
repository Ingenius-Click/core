<?php

namespace Ingenius\Core\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use PHPUnit\Framework\Assert;
use Ingenius\Core\Providers\CoreServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends BaseTestCase
{
    /**
     * Asserts that a variable is of a given type.
     *
     * @param string $expected
     * @param mixed  $actual
     * @param string $message
     */
    public function assertInstanceOf($expected, $actual, $message = '')
    {
        Assert::assertInstanceOf($expected, $actual, $message);
    }

    /**
     * Asserts that two variables are equal.
     *
     * @param mixed  $expected
     * @param mixed  $actual
     * @param string $message
     */
    public function assertEquals($expected, $actual, $message = '')
    {
        Assert::assertEquals($expected, $actual, $message);
    }

    /**
     * Asserts that a condition is true.
     *
     * @param bool   $condition
     * @param string $message
     */
    public function assertTrue($condition, $message = '')
    {
        Assert::assertTrue($condition, $message);
    }

    /**
     * Asserts that a condition is false.
     *
     * @param bool   $condition
     * @param string $message
     */
    public function assertFalse($condition, $message = '')
    {
        Assert::assertFalse($condition, $message);
    }

    /**
     * Asserts that a variable is null.
     *
     * @param mixed  $actual
     * @param string $message
     */
    public function assertNull($actual, $message = '')
    {
        Assert::assertNull($actual, $message);
    }

    /**
     * Asserts that a variable is not null.
     *
     * @param mixed  $actual
     * @param string $message
     */
    public function assertNotNull($actual, $message = '')
    {
        Assert::assertNotNull($actual, $message);
    }

    /**
     * Asserts that a variable is empty.
     *
     * @param mixed  $actual
     * @param string $message
     */
    public function assertEmpty($actual, $message = '')
    {
        Assert::assertEmpty($actual, $message);
    }

    /**
     * Asserts that a variable is not empty.
     *
     * @param mixed  $actual
     * @param string $message
     */
    public function assertNotEmpty($actual, $message = '')
    {
        Assert::assertNotEmpty($actual, $message);
    }

    /**
     * Asserts the number of elements of an array, Countable or Traversable.
     *
     * @param int    $expectedCount
     * @param mixed  $haystack
     * @param string $message
     */
    public function assertCount($expectedCount, $haystack, $message = '')
    {
        Assert::assertCount($expectedCount, $haystack, $message);
    }

    protected function getPackageProviders($app)
    {
        return [
            CoreServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Setup tenancy configuration
        $app['config']->set('tenancy.tenant_model', \Ingenius\Core\Models\Tenant::class);
        $app['config']->set('tenancy.domain_model', \Stancl\Tenancy\Database\Models\Domain::class);
    }

    // Add any common test functionality here
}
