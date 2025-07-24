<?php

namespace Ingenius\Core\Tests\Feature;

use Illuminate\Support\Facades\File;
use Ingenius\Core\Console\Commands\InstallCommand;
use PHPUnit\Framework\TestCase;

class AuthConfigurationTest extends TestCase
{
    protected $tempAuthFilePath;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a temporary auth.php file for testing
        $this->tempAuthFilePath = __DIR__ . '/temp_auth.php';

        // Sample content similar to auth.php
        $content = <<<'EOT'
<?php

return [
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];
EOT;

        File::put($this->tempAuthFilePath, $content);
    }

    protected function tearDown(): void
    {
        // Clean up the temporary file
        if (File::exists($this->tempAuthFilePath)) {
            File::delete($this->tempAuthFilePath);
        }

        parent::tearDown();
    }

    /** @test */
    public function it_updates_auth_file_with_tenant_configuration()
    {
        // Create a mock for the InstallCommand
        $command = $this->createMock(InstallCommand::class);

        // Create a reflection method to access the protected method
        $reflectionClass = new \ReflectionClass(InstallCommand::class);
        $method = $reflectionClass->getMethod('updateAuthConfiguration');
        $method->setAccessible(true);

        // Create a reflection property for config_path
        $configPathProperty = $reflectionClass->getProperty('tempAuthFilePath');
        $configPathProperty->setAccessible(true);
        $configPathProperty->setValue($command, $this->tempAuthFilePath);

        // Call the method
        $method->invoke($command);

        // Read the updated file
        $updatedContent = File::get($this->tempAuthFilePath);

        // Assert that the tenant configuration was added
        $this->assertStringContainsString("'tenant' => [", $updatedContent);
        $this->assertStringContainsString("'driver' => 'session'", $updatedContent);
        $this->assertStringContainsString("'provider' => 'tenant_users'", $updatedContent);
        $this->assertStringContainsString("'tenant_users' => [", $updatedContent);
        $this->assertStringContainsString("'model' => \Ingenius\Auth\Models\User::class", $updatedContent);
        $this->assertStringContainsString("'passwords' => [", $updatedContent);
        $this->assertStringContainsString("'tenant_users' => [", $updatedContent);
    }
}
