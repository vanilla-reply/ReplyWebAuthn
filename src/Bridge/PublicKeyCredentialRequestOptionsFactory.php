<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge;

use Reply\WebAuthn\Configuration\ConfigurationService;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialRequestOptions;

class PublicKeyCredentialRequestOptionsFactory
{
    /**
     * @var ConfigurationService
     */
    private $configService;

    public function __construct(ConfigurationService $configService)
    {
        $this->configService = $configService;
    }

    /**
     * @param PublicKeyCredentialDescriptor[] $descriptors
     */
    public function create(string $hostname, array $descriptors): PublicKeyCredentialRequestOptions
    {
        $config = $this->configService->get();

        return new PublicKeyCredentialRequestOptions(
            Challenge::generate(),
            $config->getTimeout(),
            $hostname,
            $descriptors,
            $config->getUserVerification()
        );
    }
}
