<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge;

use Cose\Algorithm\Manager;
use Reply\WebAuthn\Configuration\ConfigurationReader;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

class PublicKeyCredentialCreationOptionsFactory
{
    /**
     * @var Manager
     */
    private $algorithmManager;

    /**
     * @var ConfigurationReader
     */
    private $configurationReader;

    /**
     * @param Manager $algorithmManager
     * @param ConfigurationReader $configurationReader
     */
    public function __construct(Manager $algorithmManager, ConfigurationReader $configurationReader)
    {
        $this->algorithmManager = $algorithmManager;
        $this->configurationReader = $configurationReader;
    }

    /**
     * @param string $hostName
     * @param string $userName
     * @param string $userId
     * @return PublicKeyCredentialCreationOptions
     */
    public function create(string $hostName, string $userName, string $userId): PublicKeyCredentialCreationOptions
    {
        $rpEntity = new PublicKeyCredentialRpEntity(
            $hostName,
            $hostName,
            null
        );

        $userEntity = new PublicKeyCredentialUserEntity(
            $userName,
            $userId,
            $userName,
            null
        );

        $publicKeyCredentialParametersList = [];
        foreach ($this->algorithmManager->all() as $algorithm) {
            $publicKeyCredentialParametersList[] = new PublicKeyCredentialParameters(
                PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
                $algorithm::identifier()
            );
        }

        $config = $this->configurationReader->read();

        $authenticatorSelectionCriteria = new AuthenticatorSelectionCriteria(
            null,
            $config->isResidentKeyRequired(),
            $config->getUserVerification()
        );

        return new PublicKeyCredentialCreationOptions(
            $rpEntity,
            $userEntity,
            Challenge::generate(),
            $publicKeyCredentialParametersList,
            $config->getTimeout() * 1000,
            [],
            $authenticatorSelectionCriteria,
            $config->getAttestation(),
            null
        );
    }
}
