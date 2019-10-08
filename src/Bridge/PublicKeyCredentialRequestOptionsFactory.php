<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge;

use Reply\WebAuthn\Configuration\ConfigurationReader;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialRequestOptions;

class PublicKeyCredentialRequestOptionsFactory
{
    /**
     * @var ConfigurationReader
     */
    private $configurationReader;

    /**
     * @param ConfigurationReader $configurationReader
     */
    public function __construct(ConfigurationReader $configurationReader)
    {
        $this->configurationReader = $configurationReader;
    }

    /**
     * @param PublicKeyCredentialDescriptor[] $descriptors
     */
    public function create(string $hostname, array $descriptors): PublicKeyCredentialRequestOptions
    {
        $config = $this->configurationReader->read();

        return new PublicKeyCredentialRequestOptions(
            Challenge::generate(),
            $config->getTimeout() * 1000,
            $hostname,
            $descriptors,
            $config->getUserVerification()
        );
    }
}
