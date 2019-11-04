<?php declare(strict_types=1);

namespace Reply\WebAuthn\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1567101825CustomerCredentials extends MigrationStep
{
    /**
     * get creation timestamp
     */
    public function getCreationTimestamp(): int
    {
        return 1567101825;
    }

    /**
     * update non-destructive changes
     */
    public function update(Connection $connection): void
    {
        $connection->executeQuery('
            CREATE TABLE `customer_credential` (
              `id` varbinary(255) NOT NULL PRIMARY KEY,
              `user_handle` binary(16),
              `name` varchar(255) NOT NULL,
              `type` varchar(255) NOT NULL,
              `transports` json NOT NULL,
              `attestation_type` varchar(255) NOT NULL,
              `trust_path` json NOT NULL,
              `aaguid` varchar(255) NOT NULL,
              `public_key` varbinary(255) NOT NULL,
              `counter` int(11) NOT NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3),
              CONSTRAINT `fk.customer_credential.user_handle` FOREIGN KEY (`user_handle`)
                REFERENCES `customer` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
              CONSTRAINT `uniq.customer_credential.public_key`
                UNIQUE (`public_key`),
              CONSTRAINT `json.customer_credential.transports`
                CHECK (JSON_VALID(`transports`)),
              CONSTRAINT `json.customer_credential.trust_path`
                CHECK (JSON_VALID(`trust_path`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    /**
     * update destructive changes
     */
    public function updateDestructive(Connection $connection): void
    {
        // Nothing
    }
}
