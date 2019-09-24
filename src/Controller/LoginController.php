<?php declare(strict_types=1);

namespace Reply\WebAuthn\Controller;

use GuzzleHttp\Psr7\ServerRequest;
use Reply\WebAuthn\Bridge\CustomerCredentialRepository;
use Reply\WebAuthn\Bridge\PublicKeyCredentialDescriptorFakeFactory;
use Reply\WebAuthn\Bridge\PublicKeyCredentialRequestOptionsFactory;
use Shopware\Core\Checkout\Customer\Exception\CustomerNotFoundException;
use Shopware\Core\Checkout\Customer\SalesChannel\AccountService;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
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

    public function __construct(AccountService $accountService, CustomerCredentialRepository $credentialRepository, PublicKeyCredentialDescriptorFakeFactory $fakeFactory, PublicKeyCredentialRequestOptionsFactory $requestOptionsFactory, PublicKeyCredentialLoader $credentialLoader, AuthenticatorAssertionResponseValidator $authenticatorAssertionResponseValidator)
    {
        $this->accountService = $accountService;
        $this->credentialRepository = $credentialRepository;
        $this->fakeFactory = $fakeFactory;
        $this->requestOptionsFactory = $requestOptionsFactory;
        $this->credentialLoader = $credentialLoader;
        $this->authenticatorAssertionResponseValidator = $authenticatorAssertionResponseValidator;
    }

    /**
     * @Route("/account/webauthn/login/init", name="frontend.account.webauthn.login.init", methods={"POST"}, defaults={"XmlHttpRequest"=true})
     */
    public function init(Request $request, SalesChannelContext $context): Response
    {
        $username = $request->request->get('username');

        try {
            $customer = $this->accountService->getCustomerByEmail($username, $context);

            $descriptors = [];
            foreach ($this->credentialRepository->findAllByCustomerId($customer->getId()) as $credentialSource) {
                $descriptors[] = $credentialSource->getPublicKeyCredentialDescriptor();
            }

        } catch (CustomerNotFoundException $e) {
            // Proceed with fake data to prevent user discovery by requesting this endpoint with random usernames
            $descriptors = [$this->fakeFactory->create($username)];
        }

        $requestOptions = $this->requestOptionsFactory->create($request->getHost(), $descriptors);

        $this->getSession()->set(self::USERNAME_SESSION_KEY, $username);
        $this->getSession()->set(self::REQUEST_OPTIONS_SESSION_KEY, json_encode($requestOptions));

        return new JsonResponse($requestOptions);
    }

    /**
     * @Route("/account/webauthn/login/finalize", name="frontend.account.webauthn.login.finalize", methods={"POST"}, defaults={"XmlHttpRequest"=true})
     */
    public function finalize(Request $request, SalesChannelContext $context): Response
    {
        //$credential = $this->credentialLoader->loadArray($request->request->all());
        $credential = $this->credentialLoader->load($request->getContent());

        if (!$credential->getResponse() instanceof AuthenticatorAssertionResponse) {
            return $this->denyAccess('Response is not an AuthenticatorAssertionResponse');
        }

        $requestOptionsJson = $this->getSession()->get(self::REQUEST_OPTIONS_SESSION_KEY);
        if (!is_string($requestOptionsJson)) {
            return $this->denyAccess('Missing request options');
        }

        $username = $this->getSession()->get(self::USERNAME_SESSION_KEY);
        $customer = $this->accountService->getCustomerByEmail($username, $context);

        /** @var PublicKeyCredentialRequestOptions $requestOptions */
        $requestOptions = PublicKeyCredentialRequestOptions::createFromString($requestOptionsJson);
        $psrRequest = ServerRequest::fromGlobals();

        $this->authenticatorAssertionResponseValidator->check(
            $credential->getRawId(),
            $credential->getResponse(),
            $requestOptions,
            $psrRequest,
            $customer->getId()
        );

        $this->accountService->login($customer->getEmail(), $context);

        $this->getSession()->remove(self::USERNAME_SESSION_KEY);
        $this->getSession()->remove(self::REQUEST_OPTIONS_SESSION_KEY);

        return new JsonResponse();
    }
}
