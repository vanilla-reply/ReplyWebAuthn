<?php declare(strict_types=1);

namespace Reply\WebAuthn\Page\Account\Credential;

use Reply\WebAuthn\Bridge\PublicKeyCredentialSource;
use Shopware\Storefront\Page\Page;

class AccountCredentialPage extends Page
{
    /**
     * @var PublicKeyCredentialSource[]
     */
    protected $credentials;

    /**
     * @return PublicKeyCredentialSource[]
     */
    public function getCredentials(): array
    {
        return $this->credentials;
    }

    /**
     * @param PublicKeyCredentialSource[] $credentials
     */
    public function setCredentials(array $credentials): void
    {
        $this->credentials = $credentials;
    }
}
