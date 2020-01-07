<?php declare(strict_types=1);

namespace Reply\WebAuthn\Controller;

use Reply\WebAuthn\Bridge\CustomerCredentialRepository;
use Reply\WebAuthn\Bridge\EntityConverter;
use Reply\WebAuthn\Bridge\PublicKeyCredentialDescriptorFakeFactory;
use Reply\WebAuthn\Bridge\PublicKeyCredentialRequestOptionsFactory;
use Shopware\Core\Checkout\Customer\Exception\CustomerNotFoundException;
use Shopware\Core\Checkout\Customer\SalesChannel\AccountService;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialRequestOptions;

/**
 * @RouteScope(scopes={"storefront"})
 */
class LoginController extends AbstractController
{
    private const REQUEST_OPTIONS_SESSION_KEY = 'WebAuthnCredentialRequestOptions';
    private const USERNAME_SESSION_KEY = 'WebAuthnUsername';

    /**
     * @var AccountService
     */
    private $accountService;

    /**
     * @var CustomerCredentialRepository
     */
    private $credentialRepository;

    /**
     * @var PublicKeyCredentialDescriptorFakeFactory
     */
    private $fakeFactory;

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
     * @var HttpMessageFactoryInterface
     */
    private $httpMessageFactory;

    /**
     * @param AccountService $accountService
     * @param CustomerCredentialRepository $credentialRepository
     * @param PublicKeyCredentialDescriptorFakeFactory $fakeFactory
     * @param PublicKeyCredentialRequestOptionsFactory $requestOptionsFactory
     * @param PublicKeyCredentialLoader $credentialLoader
     * @param AuthenticatorAssertionResponseValidator $authenticatorAssertionResponseValidator
     * @param HttpMessageFactoryInterface $httpMessageFactory
     */
    public function __construct(
        AccountService $accountService,
        CustomerCredentialRepository $credentialRepository,
        PublicKeyCredentialDescriptorFakeFactory $fakeFactory,
        PublicKeyCredentialRequestOptionsFactory $requestOptionsFactory,
        PublicKeyCredentialLoader $credentialLoader,
        AuthenticatorAssertionResponseValidator $authenticatorAssertionResponseValidator,
        HttpMessageFactoryInterface $httpMessageFactory
    ) {
        $this->accountService = $accountService;
        $this->credentialRepository = $credentialRepository;
        $this->fakeFactory = $fakeFactory;
        $this->requestOptionsFactory = $requestOptionsFactory;
        $this->credentialLoader = $credentialLoader;
        $this->authenticatorAssertionResponseValidator = $authenticatorAssertionResponseValidator;
        $this->httpMessageFactory = $httpMessageFactory;
    }

    /**
     * @Route("/account/webauthn/login/init", name="frontend.account.webauthn.login.init", methods={"POST"}, defaults={"csrf_protected"=false, "XmlHttpRequest"=true})
     *
     * @param Request $request
     * @param SalesChannelContext $context
     * @return Response
     */
    public function init(Request $request, SalesChannelContext $context): Response
    {
        $username = $request->request->get('username');
        if (empty($username)) {
            return $this->createErrorResponse('Missing request parameter "username"');
        }

        try {
            $customer = $this->accountService->getCustomerByEmail($username, $context);
            $userEntity = EntityConverter::toUserEntity($customer);
            $descriptors = [];
            foreach ($this->credentialRepository->findAllForUserEntity($userEntity) as $credentialSource) {
                $descriptors[] = $credentialSource->getPublicKeyCredentialDescriptor();
            }
        } catch (CustomerNotFoundException $e) {
            // Proceed with fake data to prevent user discovery by requesting this endpoint with random usernames
            $descriptors = [$this->fakeFactory->create($username)];
        }

        $requestOptions = $this->requestOptionsFactory->create($request->getHost(), $descriptors);

        $request->getSession()->set(self::USERNAME_SESSION_KEY, $username);
        $request->getSession()->set(self::REQUEST_OPTIONS_SESSION_KEY, json_encode($requestOptions));

        return new JsonResponse($requestOptions);
    }

    /**
     * @Route("/account/webauthn/login/finalize", name="frontend.account.webauthn.login.finalize", methods={"POST"}, defaults={"csrf_protected"=false, "XmlHttpRequest"=true})
     *
     * @param Request $request
     * @param SalesChannelContext $context
     * @return Response
     */
    public function finalize(Request $request, SalesChannelContext $context): Response
    {
        $credential = $this->credentialLoader->load($request->getContent());

        if (!$credential->getResponse() instanceof AuthenticatorAssertionResponse) {
            return $this->createErrorResponse('Authenticator response does not contain assertion.');
        }

        $requestOptionsJson = $request->getSession()->get(self::REQUEST_OPTIONS_SESSION_KEY);
        $username = $request->getSession()->get(self::USERNAME_SESSION_KEY);
        if (!is_string($requestOptionsJson) || !is_string($username)) {
            return $this->createErrorResponse('Login has not been initialized properly.');
        }

        /** @var PublicKeyCredentialRequestOptions $requestOptions */
        $requestOptions = PublicKeyCredentialRequestOptions::createFromString($requestOptionsJson);

        try {
            $customer = $this->accountService->getCustomerByEmail($username, $context);
            $userEntity = EntityConverter::toUserEntity($customer);
            $userHandle = $userEntity->getId();
        } catch (CustomerNotFoundException $e) {
            // Proceed with random userHandle to prevent user discovery attacks
            $userHandle = Uuid::randomBytes();
        }

        try {
            $this->authenticatorAssertionResponseValidator->check(
                $credential->getRawId(),
                $credential->getResponse(),
                $requestOptions,
                $this->httpMessageFactory->createRequest($request),
                $userHandle
            );
        } catch (\Exception $e) {
            return $this->createErrorResponse('Authentication failed');
        }

        $this->accountService->login($username, $context);

        $request->getSession()->remove(self::USERNAME_SESSION_KEY);
        $request->getSession()->remove(self::REQUEST_OPTIONS_SESSION_KEY);

        return new JsonResponse();
    }
}
