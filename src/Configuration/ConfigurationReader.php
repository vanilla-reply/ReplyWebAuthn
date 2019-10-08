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

    public function __construct(SystemConfigService $systemConfigService)
    {
        $this->systemConfigService = $systemConfigService;
    }

    public function read(): Configuration
    {
        return new Configuration($this->systemConfigService->get(ReplyWebAuthn::CONFIG_PREFIX) ?? []);
    }
}
