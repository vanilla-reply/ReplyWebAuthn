<?php declare(strict_types=1);

namespace Reply\WebAuthn;

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

use Doctrine\DBAL\Connection;
use RuntimeException;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use function Aws\map;

class ReplyWebAuthn extends Plugin
{
    public const CONFIG_PREFIX = 'ReplyWebAuthn.config';

    /**
     * @param InstallContext $installContext
     */
    public function install(InstallContext $installContext): void
    {
        if (!extension_loaded('gmp')) {
            throw new RuntimeException('Missing required PHP extension gmp');
        }

        parent::install($installContext);

        $connection = $this->container->get(Connection::class);
        $this->createTables($connection);
        $this->writeDefaultConfig();
    }

    /**
     * @param UninstallContext $context
     */
    public function uninstall(UninstallContext $context): void
    {
        parent::uninstall($context);
        if ($context->keepUserData()) {
            return;
        }

        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);
        $this->dropTables($connection);
        $this->clearConfig($connection);
    }

    /**
     * Writes default configuration
     */
    private function writeDefaultConfig(): void
    {
        $systemConfigService = $this->container->get(SystemConfigService::class);

        foreach (self::getDefaultConfig() as $key => $value) {
            $prefixedKey = self::CONFIG_PREFIX . '.' .  $key;
            if ($systemConfigService->get($prefixedKey) === null) {
                $systemConfigService->set($prefixedKey, $value);
            }
        }
    }

    /**
     * @param Connection $connection
     */
    private function clearConfig(Connection $connection): void
    {
        $query = $connection->createQueryBuilder();
        $query
            ->delete('system_config')
            ->where('configuration_key LIKE :key')
            ->setParameter(':key', self::CONFIG_PREFIX . '.%');

        $query->execute();
    }

    /**
     * @param Connection $connection
     */
    private function dropTables(Connection $connection): void
    {
        $tables = [
            'customer_credential',
            'user_credential',
            'webauthn_credential',
            'webauthn_creation_options',
            'webauthn_request_options'
        ];

        foreach ($tables as $table) {
            $connection->executeQuery("DROP TABLE IF EXISTS `$table`");
        }
    }

    private function createTables(Connection $connection): void
    {
        $connection->executeQuery('
            CREATE TABLE IF NOT EXISTS `webauthn_credential` (
              `id` binary(16) NOT NULL PRIMARY KEY,
              `external_id` varbinary(255) NOT NULL,
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
              CONSTRAINT `uniq.webauthn_credential.public_key`
                UNIQUE (`public_key`),
              CONSTRAINT `json.webauthn_credential.transports`
                CHECK (JSON_VALID(`transports`)),
              CONSTRAINT `json.webauthn_credential.trust_path`
                CHECK (JSON_VALID(`trust_path`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeQuery('
            CREATE TABLE IF NOT EXISTS `webauthn_creation_options` (
              `user_handle` binary(16) NOT NULL PRIMARY KEY,
              `payload` json NOT NULL,
              `created_at` datetime(3) NOT NULL,
              CONSTRAINT `json.webauthn_creation_options.payload`
                CHECK (JSON_VALID(`payload`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeQuery('
            CREATE TABLE IF NOT EXISTS `webauthn_request_options` (
              `user_handle` binary(16) NOT NULL PRIMARY KEY,
              `payload` json NOT NULL,
              `created_at` datetime(3) NOT NULL,
              CONSTRAINT `json.webauthn_request_options.payload`
                CHECK (JSON_VALID(`payload`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    /**
     * @return array
     */
    public static function getDefaultConfig(): array
    {
        return [
            'timeout' => 20,
            'attestation' => 'direct',
            'userVerification' => 'preferred',
            'requireResidentKey' => false,
            'attestationStatementFormats' => [
                'android-key', 'fido-u2f', 'none', 'tpm', 'packed'
            ],
            'algorithms' => [
                'es256',
                'es384',
                'es512',
                'rs256',
                'rs384',
                'rs512'
            ],
            'allowMultipleCredentialsPerAuthenticator' => true
        ];
    }
}
