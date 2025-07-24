<?php

namespace Ingenius\Core\Bootstrappers;

use Ingenius\Core\Session\TenantDatabaseSessionHandler;
use Illuminate\Session\DatabaseSessionHandler;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;
use Stancl\Tenancy\Database\DatabaseManager;

class DatabaseSessionTenancyBootstrapper implements TenancyBootstrapper
{
    /**
     * @var DatabaseManager
     */
    protected $database;

    /**
     * The original session handler.
     *
     * @var \SessionHandlerInterface
     */
    protected $originalHandler;

    /**
     * @param DatabaseManager $database
     */
    public function __construct(DatabaseManager $database)
    {
        $this->database = $database;
    }

    /**
     * Bootstrap session for tenant.
     *
     * @param Tenant $tenant
     * @return void
     */
    public function bootstrap(Tenant $tenant): void
    {
        // Get the current session store
        $sessionStore = Session::driver();

        // Store the original handler for later restoration
        $this->originalHandler = $sessionStore->getHandler();

        // Get session configuration
        $table = Config::get('session.table', 'sessions');
        $minutes = Config::get('session.lifetime', 120);
        $container = app();

        // Get database connections
        $tenantConnection = DB::connection('tenant');
        $centralConnection = DB::connection(Config::get('tenancy.database.central_connection'));

        // Create our custom session handler
        $handler = new TenantDatabaseSessionHandler(
            $tenantConnection,
            $centralConnection,
            $table,
            $minutes,
            $container
        );

        // Directly replace the session handler
        $sessionStore->setHandler($handler);
    }

    /**
     * Revert session configuration and clean up central sessions.
     *
     * @return void
     */
    public function revert(): void
    {
        // Get current session ID and handler
        $sessionId = Session::getId();
        $sessionStore = Session::driver();
        $handler = $sessionStore->getHandler();

        // If we're using our custom handler, check and clean up central sessions
        if ($handler instanceof TenantDatabaseSessionHandler && $sessionId) {
            // Check if the session exists in the central database and delete it if it does
            if ($handler->existsInCentralDatabase($sessionId)) {
                $handler->deleteFromCentralDatabase($sessionId);
            }
        }

        // Restore the original session handler if we stored one
        if ($this->originalHandler) {
            $sessionStore->setHandler($this->originalHandler);
        }
    }
}
