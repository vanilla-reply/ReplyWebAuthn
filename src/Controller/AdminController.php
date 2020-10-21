<?php declare(strict_types=1);

namespace Reply\WebAuthn\Controller;

use Reply\WebAuthn\Bridge\CredentialRegistrationService;
use Reply\WebAuthn\Bridge\UserIdResolver;
use Reply\WebAuthn\Bridge\UserVerificationService;
use Reply\WebAuthn\Exception\CredentialRegistrationFailedException;
use Shopware\Core\Framework\Api\Context\AdminApiSource;
use Shopware\Core\Framework\Api\Context\Exception\InvalidContextSourceException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\User\UserEntity;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Webauthn\PublicKeyCredentialUserEntity;

/**
 * @RouteScope(scopes={"api"})
 */
class AdminController
{
    /**
     * @var EntityRepositoryInterface
     */
    private $userRepository;

    /**
     * @var UserVerificationService
     */
    private $userVerificationService;

    /**
     * @var CredentialRegistrationService
     */
    private $credentialRegistrationService;

    /**
     * @var HttpMessageFactoryInterface
     */
    private $httpMessageFactory;

    /** @var UserIdResolver */
    private $userIdResolver;

    public function __construct(
        EntityRepositoryInterface $userRepository,
        UserVerificationService $userVerificationService,
        CredentialRegistrationService $credentialRegistrationService,
        HttpMessageFactoryInterface $httpMessageFactory,
        UserIdResolver $userIdResolver
    ) {
        $this->userRepository = $userRepository;
        $this->userVerificationService = $userVerificationService;
        $this->credentialRegistrationService = $credentialRegistrationService;
        $this->httpMessageFactory = $httpMessageFactory;
        $this->userIdResolver = $userIdResolver;
    }

    /**
     * @Route("/api/v{version}/_action/reply-webauthn/login-options", name="api.action.reply_webauthn.login-options", methods={"POST"})
     */
    public function generateLoginOptions(Request $request, Context $context): JsonResponse
    {
        $userId = $this->userIdResolver->getUserIdByName($request->request->get('username'));

        $options = $this->userVerificationService->challenge(
            $this->httpMessageFactory->createRequest($request),
            $userId
        );

        return new JsonResponse($options);
    }

    /**
     * @Route("/api/v{version}/_action/reply-webauthn/creation-options", name="api.action.reply_webauthn.creation-options", methods={"POST"})
     */
    public function generateCreationOptions(Request $request, Context $context): JsonResponse
    {
        $user = $this->getUserFromContext($context);

        $options = $this->credentialRegistrationService->challenge(
            $this->httpMessageFactory->createRequest($request),
            $this->convertUser($user)
        );

        return new JsonResponse($options);
    }

    /**
     * @Route("/api/v{version}/_action/reply-webauthn/register-credential", name="api.action.reply_webauthn.register-credential", methods={"POST"})
     */
    public function registerCredential(Request $request, Context $context): JsonResponse
    {
        $user = $this->getUserFromContext($context);

        $this->credentialRegistrationService->register(
            $this->httpMessageFactory->createRequest($request),
            $this->convertUser($user)
        );

        return new JsonResponse();
    }



    private function getUserFromContext(Context $context): UserEntity
    {
        if (!$context->getSource() instanceof AdminApiSource) {
            throw new InvalidContextSourceException(AdminApiSource::class, get_class($context->getSource()));
        }

        $userId = $context->getSource()->getUserId();
        $user = null;

        if ($userId !== null) {
            $criteria = new Criteria([$userId]);
            $user = $this->userRepository->search($criteria, $context)->getEntities()->first();
        }

        if ($user === null) {
            throw new CredentialRegistrationFailedException('Cannot find user.');
        }

        return $user;
    }

    private function convertUser(UserEntity $swUser): PublicKeyCredentialUserEntity
    {
        return new PublicKeyCredentialUserEntity(
            $swUser->getUsername(),
            hex2bin($swUser->getId()),
            $swUser->getUsername()
        );
    }
}
