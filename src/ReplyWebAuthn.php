<?php declare(strict_types=1);

namespace Reply\WebAuthn;

use Doctrine\DBAL\Connection;
use Reply\WebAuthn\Bridge\DoctrineCustomerCredentialRepository;
use Reply\WebAuthn\Configuration\Configuration;
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
        $config = new Configuration([]);
        $systemConfigService = $this->container->get(SystemConfigService::class);

        foreach ($config->getIterator() as $key => $value) {
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
        foreach ($this->getTables() as $table) {
            $connection->executeQuery("DROP TABLE IF EXISTS `$table`");
        }
    }

    /**
     * @return array
     */
    private function getTables(): array
    {
        return [
            DoctrineCustomerCredentialRepository::TABLE_NAME
        ];
    }
}
