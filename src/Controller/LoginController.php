<?php declare(strict_types=1);

namespace Reply\WebAuthn\Controller;

use Reply\WebAuthn\Bridge\UserVerificationService;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Customer\SalesChannel\AccountService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Routing\Exception\MissingRequestParameterException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"storefront"})
 */
class LoginController extends StorefrontController
{
    /**
     * @var UserVerificationService
     */
    private $userVerificationService;

    /**
     * @var AccountService
     */
    private $accountService;

    /**
     * @var HttpMessageFactoryInterface
     */
    private $httpMessageFactory;

    /**
     * @var EntityRepositoryInterface $customerRepository
     */
    private $customerRepository;

    /**
     * @param UserVerificationService $userVerificationService
     * @param AccountService $accountService
     * @param HttpMessageFactoryInterface $httpMessageFactory
     * @param EntityRepositoryInterface $customerRepository
     */
    public function __construct(UserVerificationService $userVerificationService, AccountService $accountService, HttpMessageFactoryInterface $httpMessageFactory, EntityRepositoryInterface $customerRepository)
    {
        $this->userVerificationService = $userVerificationService;
        $this->accountService = $accountService;
        $this->httpMessageFactory = $httpMessageFactory;
        $this->customerRepository = $customerRepository;
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
            throw new MissingRequestParameterException('username');
        }

        $customer = $this->getCustomerByEmail($username, $context);

        $userHandle = $customer !== null ? hex2bin($customer->getId()) : null;

        $requestOptions = $this->userVerificationService->challenge(
            $this->httpMessageFactory->createRequest($request),
            $userHandle
        );

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
        $username = $request->request->get('username');
        if (empty($username)) {
            throw new MissingRequestParameterException('username');
        }

        $customer = $this->getCustomerByEmail($username, $context);

        $userHandle = $customer !== null ? hex2bin($customer->getId()): null;

        $this->userVerificationService->verify(
            $this->httpMessageFactory->createRequest($request),
            $userHandle
        );

        $this->accountService->login($username, $context);

        return new JsonResponse();
    }

    private function getCustomerByEmail($email, SalesChannelContext $context): ?CustomerEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('customer.email', $email));

        $result = $this->customerRepository->search($criteria, $context->getContext());

        return $result->first();
    }
}
