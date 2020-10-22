<?php declare(strict_types=1);

namespace Reply\WebAuthn\Controller;

use Reply\WebAuthn\Bridge\CredentialRegistrationService;
use Reply\WebAuthn\Bridge\UserIdResolver;
use Reply\WebAuthn\Bridge\UserVerificationService;
use Reply\WebAuthn\DataAbstractionLayer\Entity\WebauthnCredentialEntity;
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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

    /** @var EntityRepositoryInterface */
    private $webauthnCredentialRepository;

    public function __construct(
        EntityRepositoryInterface $userRepository,
        UserVerificationService $userVerificationService,
        CredentialRegistrationService $credentialRegistrationService,
        HttpMessageFactoryInterface $httpMessageFactory,
        UserIdResolver $userIdResolver,
        EntityRepositoryInterface $webauthnCredentialRepository
    ) {
        $this->userRepository = $userRepository;
        $this->userVerificationService = $userVerificationService;
        $this->credentialRegistrationService = $credentialRegistrationService;
        $this->httpMessageFactory = $httpMessageFactory;
        $this->userIdResolver = $userIdResolver;
        $this->webauthnCredentialRepository = $webauthnCredentialRepository;
    }

    /**
     * @Route("/api/v{version}/_action/reply-webauthn/login-options", name="api.action.reply_webauthn.login-options", defaults={"auth_required"=false}, methods={"POST"})
     */
    public function generateLoginOptions(Request $request): JsonResponse
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

        $searchResult = $this->webauthnCredentialRepository->search(new Criteria(), $context);

        return new JsonResponse($searchResult->getEntities());
    }

    /**
     * @Route("/api/v{version}/_action/reply-webauthn/delete-credential", name="api.action.reply_webauthn.delete-credential", methods={"POST"})
     */
    public function deleteCredential(Request $request, Context $context): JsonResponse
    {
        $id = $request->request->get('id');
        $searchResult = $this->webauthnCredentialRepository->search(new Criteria([$id]), $context);
        /** @var WebauthnCredentialEntity $credential */
        $credential = $searchResult->getEntities()->first();
        $user = $this->getUserFromContext($context);

        if (!$credential || $credential->getUserHandle() != $user->getId()) {
            throw new NotFoundHttpException();
        }

        $this->webauthnCredentialRepository->delete(
            [['id' => $request->request->get('id')]],
            $context
        );

        $searchResult = $this->webauthnCredentialRepository->search(new Criteria(), $context);

        return new JsonResponse($searchResult->getEntities());
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
