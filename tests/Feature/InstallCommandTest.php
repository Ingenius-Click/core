<?php

namespace Ingenius\Core\Tests\Feature;

use Illuminate\Support\Facades\File;
use Ingenius\Core\Console\Commands\InstallCommand;
use PHPUnit\Framework\TestCase;

class InstallCommandTest extends TestCase
{
    protected $tempAppFilePath;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a temporary app.php file for testing
        $this->tempAppFilePath = __DIR__ . '/temp_app.php';

        // Sample content similar to bootstrap/app.php
        $content = <<<'EOT'
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware) {
        // Default middleware configuration
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
EOT;

        File::put($this->tempAppFilePath, $content);
    }

    protected function tearDown(): void
    {
        // Clean up the temporary file
        if (File::exists($this->tempAppFilePath)) {
            File::delete($this->tempAppFilePath);
        }

        parent::tearDown();
    }

    /** @test */
    public function it_updates_bootstrap_app_file_with_middleware_configuration()
    {
        // Create a mock for the InstallCommand
        $command = $this->createMock(InstallCommand::class);

        // Create a reflection method to access the protected method
        $reflectionClass = new \ReflectionClass(InstallCommand::class);
        $method = $reflectionClass->getMethod('updateBootstrapAppFile');
        $method->setAccessible(true);

        // Override the base_path function for testing
        $basePath = $this->tempAppFilePath;

        // Use reflection to set private properties
        $reflectionProperty = $reflectionClass->getProperty('tempAppFilePath');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($command, $basePath);

        // Call the method
        $method->invoke($command);

        // Read the updated file
        $updatedContent = File::get($this->tempAppFilePath);

        // Assert that the middleware configuration was added
        $this->assertStringContainsString('Illuminate\Session\Middleware\StartSession::class', $updatedContent);
        $this->assertStringContainsString('Illuminate\Cookie\Middleware\EncryptCookies::class', $updatedContent);
        $this->assertStringContainsString('\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class', $updatedContent);
        $this->assertStringContainsString('$middleware->statefulApi();', $updatedContent);
        $this->assertStringContainsString('$middleware->group(\'universal\', []);', $updatedContent);
    }
}
