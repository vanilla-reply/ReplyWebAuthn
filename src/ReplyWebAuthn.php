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

    public function install(InstallContext $installContext): void
    {
        parent::install($installContext);
        $this->writeDefaultConfig();
    }

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

    private function getSystemConfigService(): SystemConfigService
    {
        return new SystemConfigService(
            $this->container->get(Connection::class),
            $this->container->get('system_config.repository')
        );
    }

    private function writeDefaultConfig(): void
    {
        $config = new Configuration([]);
        $systemConfigService = $this->getSystemConfigService();

        foreach ($config->getIterator() as $key => $value) {
            $prefixedKey = self::CONFIG_PREFIX . '.' .  $key;
            if ($systemConfigService->get($prefixedKey) === null) {
                $systemConfigService->set($prefixedKey, $value);
            }
        }
    }

    private function clearConfig(Connection $connection): void
    {
        $query = $connection->createQueryBuilder();
        $query
            ->delete('system_config')
            ->where('configuration_key LIKE :key')
            ->setParameter(':key', self::CONFIG_PREFIX . '.%');

        $query->execute();
    }

    private function dropTables(Connection $connection): void
    {
        foreach ($this->getTables() as $table) {
            $connection->executeQuery("DROP TABLE IF EXISTS `$table`");
        }
    }

    private function getTables(): array
    {
        return [
            DoctrineCustomerCredentialRepository::TABLE_NAME
        ];
    }
}
