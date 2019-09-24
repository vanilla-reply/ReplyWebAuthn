<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge;

use Cose\Algorithm\Manager;
use Reply\WebAuthn\Configuration\ConfigurationService;
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
     * @var ConfigurationService
     */
    private $configService;

    public function __construct(Manager $algorithmManager, ConfigurationService $configService)
    {
        $this->algorithmManager = $algorithmManager;
        $this->configService = $configService;
    }

    public function create(string $hostName, string $userName, string $userId): PublicKeyCredentialCreationOptions
    {
        // RP Entity
        $rpEntity = new PublicKeyCredentialRpEntity(
            $hostName,
            $hostName,
            null
        );

        // User Entity
        $userEntity = new PublicKeyCredentialUserEntity(
            $userName,
            $userId,
            $userName,
            null
        );

        // Public Key Credential Parameters
        $publicKeyCredentialParametersList = [];
        foreach ($this->algorithmManager->all() as $algorithm) {
            $publicKeyCredentialParametersList[] = new PublicKeyCredentialParameters(
                PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
                $algorithm::identifier()
            );
        }

        $config = $this->configService->get();

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
            $config->getTimeout(),
            [],
            $authenticatorSelectionCriteria,
            $config->getAttestation(),
            null
        );
    }
}
