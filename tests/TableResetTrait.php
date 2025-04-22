<?php

namespace App\Tests;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

trait TableResetTrait
{
    private function truncateTables(array $tableNames): void
    {
        $container = self::getContainer();
        /** @var Connection $connection */
        $connection = $container->get(Connection::class);

        $platform = $connection->getDatabasePlatform();

        $connection->executeStatement('SET session_replication_role = replica'); // for PostgreSQL: disable FK checks

        foreach ($tableNames as $table) {
            $sql = $platform->getTruncateTableSQL($table, true); // true = cascade
            $connection->executeStatement($sql);
        }

        $connection->executeStatement('SET session_replication_role = DEFAULT'); // re-enable FK checks
    }
}
