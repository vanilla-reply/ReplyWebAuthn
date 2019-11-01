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
        $connection->executeQuery("DROP TABLE IF EXISTS `customer_credential`");
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
                'android-key', 'fido-u2f', 'none', 'tpm'
            ],
            'algorithms' => [
                'es256',
                'es384',
                'es512',
                'rs256',
                'rs384',
                'rs512'
            ]
        ];
    }
}
