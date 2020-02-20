<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge;

use Cose\Algorithm\Manager;
use Reply\WebAuthn\Configuration\Configuration;
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
     * @param PublicKeyCredentialRpEntity $rpEntity
     * @param PublicKeyCredentialUserEntity $userEntity
     * @param PublicKeyCredentialDescriptor[] $existingCredentials
     * @return PublicKeyCredentialCreationOptions
     */
    public function create(PublicKeyCredentialRpEntity $rpEntity, PublicKeyCredentialUserEntity $userEntity, array $existingCredentials): PublicKeyCredentialCreationOptions
    {
        $challenge = new Challenge();
        $config = $this->configurationReader->read();

        return new PublicKeyCredentialCreationOptions(
            $rpEntity,
            $userEntity,
            $challenge->asBytes(),
            $this->buildCredentialParametersList(),
            $config->getTimeout() * 1000,
            $config->areMultipleCredentialsPerAuthenticatorAllowed() ? [] : $existingCredentials,
            $this->getAuthenticatorSelectionCriteria($config),
            $config->getAttestation(),
            null
        );
    }

    /**
     * @return array|PublicKeyCredentialParameters[]
     */
    private function buildCredentialParametersList(): array
    {
        $list = [];
        foreach ($this->algorithmManager->all() as $algorithm) {
            $list[] = new PublicKeyCredentialParameters(
                PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
                $algorithm::identifier()
            );
        }

        return $list;
    }

    /**
     * @param Configuration $config
     * @return AuthenticatorSelectionCriteria
     */
    private function getAuthenticatorSelectionCriteria(Configuration $config): AuthenticatorSelectionCriteria
    {
        return new AuthenticatorSelectionCriteria(
            null,
            $config->isResidentKeyRequired(),
            $config->getUserVerification()
        );
    }
}
