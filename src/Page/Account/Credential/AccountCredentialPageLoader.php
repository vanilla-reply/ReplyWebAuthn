<?php declare(strict_types=1);

namespace Reply\WebAuthn\Page\Account\Credential;

use Reply\WebAuthn\Bridge\PublicKeyCredentialSourceRepository;
use Reply\WebAuthn\Bridge\EntityConverter;
use Shopware\Core\Checkout\Cart\Exception\CustomerNotLoggedInException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\GenericPageLoader;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

class AccountCredentialPageLoader
{
    /**
     * @var PublicKeyCredentialSourceRepository
     */
    private $credentialRepository;

    /**
     * @var GenericPageLoader
     */
    private $genericLoader;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param PublicKeyCredentialSourceRepository $credentialRepository
     * @param GenericPageLoader $genericLoader
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        PublicKeyCredentialSourceRepository $credentialRepository,
        GenericPageLoader $genericLoader,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->credentialRepository = $credentialRepository;
        $this->genericLoader = $genericLoader;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Request $request
     * @param SalesChannelContext $salesChannelContext
     * @return AccountCredentialPage
     */
    public function load(Request $request, SalesChannelContext $salesChannelContext): AccountCredentialPage
    {
        if (!$salesChannelContext->getCustomer()) {
            throw new CustomerNotLoggedInException();
        }

        $page = $this->genericLoader->load($request, $salesChannelContext);

        $page = AccountCredentialPage::createFrom($page);

        $credentials = $this->credentialRepository->findAllForUserEntity(
            EntityConverter::toUserEntity($salesChannelContext->getCustomer())
        );

        $page->setCredentials($credentials);

        $this->eventDispatcher->dispatch(
            new AccountCredentialPageLoadedEvent($page, $salesChannelContext, $request)
        );

        return $page;
    }
}
