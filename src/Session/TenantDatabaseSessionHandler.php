<?php

namespace Ingenius\Core\Session;

use Illuminate\Contracts\Container\Container;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Session\DatabaseSessionHandler;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantDatabaseSessionHandler extends DatabaseSessionHandler
{
    /**
     * The tenant database connection.
     *
     * @var \Illuminate\Database\ConnectionInterface
     */
    protected $tenantConnection;

    /**
     * The central database connection.
     *
     * @var \Illuminate\Database\ConnectionInterface
     */
    protected $centralConnection;

    /**
     * The name of the session table.
     *
     * @var string
     */
    protected $table;

    /**
     * Create a new tenant database session handler instance.
     *
     * @param  \Illuminate\Database\ConnectionInterface  $tenantConnection
     * @param  \Illuminate\Database\ConnectionInterface  $centralConnection
     * @param  string  $table
     * @param  int  $minutes
     * @param  \Illuminate\Contracts\Container\Container|null  $container
     */
    public function __construct(
        ConnectionInterface $tenantConnection,
        ConnectionInterface $centralConnection,
        $table,
        $minutes,
        ?Container $container = null
    ) {
        $this->table = $table;
        $this->tenantConnection = $tenantConnection;
        $this->centralConnection = $centralConnection;

        // Call parent constructor with tenant connection
        parent::__construct($tenantConnection, $table, $minutes, $container);
    }

    /**
     * Get the database query builder for the session table.
     * This overrides the parent method to ensure we're using the tenant connection.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getQuery()
    {
        return $this->tenantConnection->table($this->table);
    }

    public function write($sessionId, $data): bool
    {
        Log::info('TenantDatabaseSessionHandler::write - Session ID: ' . $sessionId);
        Log::info('TenantDatabaseSessionHandler::write - Data: ' . $data);
        return parent::write($sessionId, $data);
    }

    /**
     * Check if the session exists in the central database.
     *
     * @param  string  $sessionId
     * @return bool
     */
    public function existsInCentralDatabase($sessionId)
    {
        return $this->centralConnection->table($this->table)
            ->where('id', $sessionId)
            ->exists();
    }

    /**
     * Delete the session from the central database.
     *
     * @param  string  $sessionId
     * @return bool
     */
    public function deleteFromCentralDatabase($sessionId)
    {
        return $this->centralConnection->table($this->table)
            ->where('id', $sessionId)
            ->delete() > 0;
    }
}
