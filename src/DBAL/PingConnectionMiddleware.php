<?php

declare(strict_types=1);

namespace League\Tactician\Doctrine\DBAL;

use Doctrine\DBAL\Connection;
use League\Tactician\Middleware;
use Throwable;

/**
 * Verifies if there is a connection established with the database. If not it will reconnect.
 */
final class PingConnectionMiddleware implements Middleware
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * Reconnects to the database if the connection is expired.
     */
    public function execute(object $command, callable $next): mixed
    {
        if (! $this->ping($this->connection)) {
            $this->connection->close();
            $this->connection->connect();
        }

        return $next($command);
    }

    private function ping(Connection $connection): bool
    {
        try {
            $dummySelectSQL = $connection->getDatabasePlatform()->getDummySelectSQL();

            $connection->executeQuery($dummySelectSQL);
        } catch (Throwable) {
            return false;
        }

        return true;
    }
}
