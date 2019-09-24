<?php declare(strict_types=1);

namespace Reply\WebAuthn\Configuration;

use Shopware\Core\System\SystemConfig\SystemConfigService;

class ConfigurationService
{
    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    /**
     * @var string
     */
    private $keyPrefix;

    public function __construct(SystemConfigService $systemConfigService, string $keyPrefix)
    {
        $this->systemConfigService = $systemConfigService;
        $this->keyPrefix = $keyPrefix;
    }

    public function get(): Configuration
    {
        return new Configuration($this->systemConfigService->get($this->keyPrefix) ?? []);
    }
}
