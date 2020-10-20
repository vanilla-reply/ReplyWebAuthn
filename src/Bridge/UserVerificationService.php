<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge;

use Exception;
use Psr\Http\Message\ServerRequestInterface;
use Reply\WebAuthn\Exception\AuthFailedException;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialUserEntity;

class UserVerificationService
{
    /**
     * @var PublicKeyCredentialSourceRepository
     */
    private $credentialRepository;

    /**
     * @var PublicKeyCredentialRequestOptionsFactory
     */
    private $requestOptionsFactory;

    /**
     * @var PublicKeyCredentialLoader
     */
    private $credentialLoader;

    /**
     * @var AuthenticatorAssertionResponseValidator
     */
    private $authenticatorAssertionResponseValidator;

    /**
     * @var PublicKeyCredentialDescriptorFakeFactory
     */
    private $credentialDescriptorFakeFactory;

    /**
     * @param PublicKeyCredentialSourceRepository $credentialRepository
     * @param PublicKeyCredentialRequestOptionsFactory $requestOptionsFactory
     * @param PublicKeyCredentialLoader $credentialLoader
     * @param AuthenticatorAssertionResponseValidator $authenticatorAssertionResponseValidator
     * @param PublicKeyCredentialDescriptorFakeFactory $credentialDescriptorFakeFactory
     */
    public function __construct(
        PublicKeyCredentialSourceRepository $credentialRepository,
        PublicKeyCredentialRequestOptionsFactory $requestOptionsFactory,
        PublicKeyCredentialLoader $credentialLoader,
        AuthenticatorAssertionResponseValidator $authenticatorAssertionResponseValidator,
        PublicKeyCredentialDescriptorFakeFactory $credentialDescriptorFakeFactory
    ) {
        $this->credentialRepository = $credentialRepository;
        $this->requestOptionsFactory = $requestOptionsFactory;
        $this->credentialLoader = $credentialLoader;
        $this->authenticatorAssertionResponseValidator = $authenticatorAssertionResponseValidator;
        $this->credentialDescriptorFakeFactory = $credentialDescriptorFakeFactory;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ?string $userHandle
     * @return PublicKeyCredentialRequestOptions
     */
    public function challenge(
        ServerRequestInterface $request,
        ?string $userHandle
    ): PublicKeyCredentialRequestOptions {
        $parsedBody = (array)$request->getParsedBody();
        $username = $parsedBody['username'] ?? '';
        $descriptors = [];

        if ($userHandle === null) {
            $descriptors[] = $this->credentialDescriptorFakeFactory->create($username);
        } else {
            $userEntity = new PublicKeyCredentialUserEntity($username, $userHandle, $username);
            foreach ($this->credentialRepository->findAllForUserEntity($userEntity) as $credentialSource) {
                $descriptors[] = $credentialSource->getPublicKeyCredentialDescriptor();
            }
        }

        return $this->requestOptionsFactory->create($request->getUri()->getHost(), $descriptors);
    }

    /**
     * @param ServerRequestInterface $request
     * @param PublicKeyCredentialRequestOptions $requestOptions
     * @param string|null $userHandle
     * @return string verified userHandle
     */
    public function verify(
        ServerRequestInterface $request,
        PublicKeyCredentialRequestOptions $requestOptions,
        ?string $userHandle
    ): string {
        $parsedBody = (array)$request->getParsedBody();

        $credential = $this->credentialLoader->loadArray($parsedBody['credential'] ?? []);

        $authenticatorResponse = $credential->getResponse();

        if (!($authenticatorResponse instanceof AuthenticatorAssertionResponse)) {
            throw new AuthFailedException('Authenticator response did not contain assertion.');
        }

        try {
            $credentialSource = $this->authenticatorAssertionResponseValidator->check(
                $credential->getRawId(),
                $authenticatorResponse,
                $requestOptions,
                $request,
                $userHandle
            );
        } catch (Exception $e) {
            throw new AuthFailedException('Failed to validate authenticator assertion.');
        }

        return $credentialSource->getUserHandle();
    }
}
