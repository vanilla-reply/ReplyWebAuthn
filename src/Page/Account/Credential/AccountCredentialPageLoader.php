<?php declare(strict_types=1);

namespace Reply\WebAuthn\Page\Account\Credential;

use Reply\WebAuthn\Bridge\CustomerCredentialRepository;
use Shopware\Core\Checkout\Cart\Exception\CustomerNotLoggedInException;
use Shopware\Core\Content\Category\Exception\CategoryNotFoundException;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\Routing\Exception\MissingRequestParameterException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\GenericPageLoader;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

class AccountCredentialPageLoader
{
    /**
     * @var CustomerCredentialRepository
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

    public function __construct(CustomerCredentialRepository $credentialRepository, GenericPageLoader $genericLoader, EventDispatcherInterface $eventDispatcher)
    {
        $this->credentialRepository = $credentialRepository;
        $this->genericLoader = $genericLoader;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @throws CategoryNotFoundException
     * @throws CustomerNotLoggedInException
     * @throws InconsistentCriteriaIdsException
     * @throws MissingRequestParameterException
     */
    public function load(Request $request, SalesChannelContext $salesChannelContext): AccountCredentialPage
    {
        if (!$salesChannelContext->getCustomer()) {
            throw new CustomerNotLoggedInException();
        }

        $page = $this->genericLoader->load($request, $salesChannelContext);

        $page = AccountCredentialPage::createFrom($page);

        $page->setCredentials(
            $this->credentialRepository->findAllByCustomerId($salesChannelContext->getCustomer()->getId())
        );

        $this->eventDispatcher->dispatch(
            new AccountCredentialPageLoadedEvent($page, $salesChannelContext, $request)
        );

        return $page;
    }
}
