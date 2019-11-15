<?php declare(strict_types=1);

namespace Reply\WebAuthn\Configuration;

use Reply\WebAuthn\ReplyWebAuthn;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class ConfigurationReader
{
    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    /**
     * @param SystemConfigService $systemConfigService
     */
    public function __construct(SystemConfigService $systemConfigService)
    {
        $this->systemConfigService = $systemConfigService;
    }

    /**
     * @return Configuration
     */
    public function read(): Configuration
    {
        $values = $this->systemConfigService->get(ReplyWebAuthn::CONFIG_PREFIX) ?? [];

        return new Configuration(array_merge(ReplyWebAuthn::getDefaultConfig(), $values));
    }
}
