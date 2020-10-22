<?php declare(strict_types=1);

namespace Reply\WebAuthn\Controller;

use Base64Url\Base64Url;
use Reply\WebAuthn\Bridge\CredentialRegistrationService;
use Reply\WebAuthn\Bridge\EntityConverter;
use Reply\WebAuthn\Bridge\PublicKeyCredentialSourceRepository;
use Reply\WebAuthn\Page\Account\Credential\AccountCredentialPageLoader;
use Shopware\Core\Checkout\Cart\Exception\CustomerNotLoggedInException;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"storefront"})
 */
class AccountCredentialController extends AbstractController
{
    /**
     * @var CredentialRegistrationService
     */
    private $credentialRegistrationService;

    /**
     * @var AccountCredentialPageLoader
     */
    private $pageLoader;

    /**
     * @var PublicKeyCredentialSourceRepository
     */
    private $credentialRepository;

    /**
     * @var HttpMessageFactoryInterface
     */
    private $httpMessageFactory;

    /**
     * @param CredentialRegistrationService $credentialRegistrationService
     * @param AccountCredentialPageLoader $pageLoader
     * @param PublicKeyCredentialSourceRepository $credentialRepository
     * @param HttpMessageFactoryInterface $httpMessageFactory
     */
    public function __construct(CredentialRegistrationService $credentialRegistrationService, AccountCredentialPageLoader $pageLoader, PublicKeyCredentialSourceRepository $credentialRepository, HttpMessageFactoryInterface $httpMessageFactory)
    {
        $this->credentialRegistrationService = $credentialRegistrationService;
        $this->pageLoader = $pageLoader;
        $this->credentialRepository = $credentialRepository;
        $this->httpMessageFactory = $httpMessageFactory;
    }

    /**
     * @Route("/account/credential", name="frontend.account.credential.page", options={"seo"="false"}, methods={"GET"})
     *
     * @throws CustomerNotLoggedInException
     */
    public function overviewPage(Request $request, SalesChannelContext $context): Response
    {
        $this->denyAccessUnlessLoggedIn();

        $page = $this->pageLoader->load($request, $context);

        return $this->renderStorefront('@ReplyWebAuthn/storefront/page/account/credential/index.html.twig', ['page' => $page]);
    }

    /**
     * @Route("/account/webauthn/credential/challenge", name="frontend.account.webauthn.credential.challenge", options={"seo"="false"}, methods={"POST"}, defaults={"csrf_protected"=false, "XmlHttpRequest"=true})
     */
    public function challenge(SalesChannelContext $context, Request $request): Response
    {
        $this->denyAccessUnlessLoggedIn();

        $userEntity = EntityConverter::toUserEntity($context->getCustomer());

        $options = $this->credentialRegistrationService->challenge(
            $this->httpMessageFactory->createRequest($request),
            $userEntity
        );

        return new JsonResponse($options);
    }

    /**
     * @Route("/account/webauthn/credential/register", name="frontend.account.webauthn.credential.register", options={"seo"="false"}, methods={"POST"}, defaults={"csrf_protected"=false, "XmlHttpRequest"=true})
     */
    public function register(SalesChannelContext $context, Request $request): JsonResponse
    {
        $this->denyAccessUnlessLoggedIn();

        $userEntity = EntityConverter::toUserEntity($context->getCustomer());

        $this->credentialRegistrationService->register(
            $this->httpMessageFactory->createRequest($request),
            $userEntity
        );

        return new JsonResponse();
    }

    /**
     * @Route("/account/webauthn/credential/delete/{credentialId}", name="frontend.account.webauthn.credential.delete.one", options={"csrf_protected"=true, "seo"="false"}, methods={"POST"})
     */
    public function deleteOne(string $credentialId, SalesChannelContext $salesChannelContext): Response
    {
        $this->denyAccessUnlessLoggedIn();

        $credentialId = Base64Url::decode($credentialId);
        $credential = $this->credentialRepository->findOneByCredentialId($credentialId);
        if ($credential !== null && bin2hex($credential->getUserHandle()) === $salesChannelContext->getCustomer()->getId()) {
            $this->credentialRepository->deleteById($credentialId);
        }

        return $this->redirectToRoute('frontend.account.credential.page');
    }

    /**
     * @Route("/widgets/account/webauthn/credential/creation-modal", name="frontend.account.webauthn.credential.creation-modal", options={"csrf_protected"=false, "seo"=false}, methods={"GET"}, defaults={"XmlHttpRequest"=true})
     */
    public function renderCreationModal(): Response
    {
        $this->denyAccessUnlessLoggedIn();

        return $this->renderStorefront('@ReplyWebAuthn/storefront/component/account/credential-creation-modal.html.twig');
    }
}
