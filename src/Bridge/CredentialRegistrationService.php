<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge;

use Psr\Http\Message\ServerRequestInterface;
use Reply\WebAuthn\Exception\CredentialRegistrationFailedException;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

class CredentialRegistrationService
{
    /**
     * @var PublicKeyCredentialCreationOptionsFactory
     */
    private $creationOptionsFactory;

    /**
     * @var PublicKeyCredentialCreationOptionsRepository
     */
    private $creationOptionsRepository;

    /**
     * @var PublicKeyCredentialSourceRepository
     */
    private $credentialRepository;

    /**
     * @var PublicKeyCredentialLoader
     */
    private $credentialLoader;

    /**
     * @var AuthenticatorAttestationResponseValidator
     */
    private $authenticatorAttestationResponseValidator;

    /**
     * @param PublicKeyCredentialCreationOptionsFactory $creationOptionsFactory
     * @param PublicKeyCredentialCreationOptionsRepository $creationOptionsRepository
     * @param PublicKeyCredentialSourceRepository $credentialRepository
     * @param PublicKeyCredentialLoader $credentialLoader
     * @param AuthenticatorAttestationResponseValidator $authenticatorAttestationResponseValidator
     */
    public function __construct(PublicKeyCredentialCreationOptionsFactory $creationOptionsFactory, PublicKeyCredentialCreationOptionsRepository $creationOptionsRepository, PublicKeyCredentialSourceRepository $credentialRepository, PublicKeyCredentialLoader $credentialLoader, AuthenticatorAttestationResponseValidator $authenticatorAttestationResponseValidator)
    {
        $this->creationOptionsFactory = $creationOptionsFactory;
        $this->creationOptionsRepository = $creationOptionsRepository;
        $this->credentialRepository = $credentialRepository;
        $this->credentialLoader = $credentialLoader;
        $this->authenticatorAttestationResponseValidator = $authenticatorAttestationResponseValidator;
    }

    public function challenge(
        ServerRequestInterface $request,
        PublicKeyCredentialUserEntity $userEntity
    ): PublicKeyCredentialCreationOptions {
        $existingCredentials = [];

        foreach ($this->credentialRepository->findAllForUserEntity($userEntity) as $credentialSource) {
            $existingCredentials[] = $credentialSource->getPublicKeyCredentialDescriptor();
        }

        $rpEntity = $this->getRpEntity($request);

        $options = $this->creationOptionsFactory->create($rpEntity, $userEntity, $existingCredentials);

        $this->creationOptionsRepository->save($options);

        return $options;
    }

    public function register(ServerRequestInterface $request, PublicKeyCredentialUserEntity $userEntity): void
    {
        $parsedBody = (array)$request->getParsedBody();
        $credential = $this->credentialLoader->loadArray($parsedBody['credential'] ?? []);

        if (strlen($credential->getRawId()) > 255) {
            throw new CredentialRegistrationFailedException('Credential ID exceeds maximum length of 255 bytes');
        }

        if (!isset($parsedBody['credential']['name']) || empty($parsedBody['credential']['name'])) {
            throw new CredentialRegistrationFailedException('Missing or invalid request parameter "name"');
        }

        $authenticatorResponse = $credential->getResponse();

        if (!$authenticatorResponse instanceof AuthenticatorAttestationResponse) {
            throw new CredentialRegistrationFailedException('Authenticator response does not contain attestation.');
        }

        $creationOptions = $this->creationOptionsRepository->fetch($userEntity->getId());

        if ($creationOptions === null) {
            throw new CredentialRegistrationFailedException('Could not find creation options.');
        }

        $credentialSource = $this->authenticatorAttestationResponseValidator->check(
            $authenticatorResponse,
            $creationOptions,
            $request
        );

        $entity = PublicKeyCredentialSource::createFromBase($credentialSource);
        $entity->setName((string)$parsedBody['credential']['name']);

        $this->credentialRepository->saveCredentialSource($entity);

        $this->creationOptionsRepository->deleteOne($creationOptions->getUser()->getId());
    }

    /**
     * @param ServerRequestInterface $request
     * @return PublicKeyCredentialRpEntity
     */
    private function getRpEntity(ServerRequestInterface $request): PublicKeyCredentialRpEntity
    {
        $host = $request->getUri()->getHost();

        return new PublicKeyCredentialRpEntity(
            $host,
            $host,
            null
        );
    }
}
