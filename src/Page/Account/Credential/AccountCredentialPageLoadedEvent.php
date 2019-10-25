<?php declare(strict_types=1);

namespace Reply\WebAuthn\Page\Account\Credential;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\NestedEvent;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

class AccountCredentialPageLoadedEvent extends NestedEvent
{
    /**
     * @var AccountCredentialPage
     */
    protected $page;

    /**
     * @var SalesChannelContext
     */
    protected $context;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @param AccountCredentialPage $page
     * @param SalesChannelContext $salesChannelContext
     * @param Request $request
     */
    public function __construct(AccountCredentialPage $page, SalesChannelContext $salesChannelContext, Request $request)
    {
        $this->page = $page;
        $this->context = $salesChannelContext;
        $this->request = $request;
    }

    /**
     * @return Context
     */
    public function getContext(): Context
    {
        return $this->context->getContext();
    }

    /**
     * @return SalesChannelContext
     */
    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->context;
    }

    /**
     * @return AccountCredentialPage
     */
    public function getPage(): AccountCredentialPage
    {
        return $this->page;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }
}
