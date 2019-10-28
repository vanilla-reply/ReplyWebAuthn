<?php declare(strict_types=1);

namespace Reply\WebAuthn\Page\Account\Credential;

use Reply\WebAuthn\Bridge\PublicKeyCredentialEntity;
use Shopware\Storefront\Page\Page;

class AccountCredentialPage extends Page
{
    /**
     * @var PublicKeyCredentialEntity[]
     */
    protected $credentials;

    /**
     * @return PublicKeyCredentialEntity[]
     */
    public function getCredentials(): array
    {
        return $this->credentials;
    }

    /**
     * @param PublicKeyCredentialEntity[] $credentials
     */
    public function setCredentials(array $credentials): void
    {
        $this->credentials = $credentials;
    }
}
