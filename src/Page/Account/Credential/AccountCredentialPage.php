<?php declare(strict_types=1);

namespace Reply\WebAuthn\Page\Account\Credential;

use Shopware\Storefront\Page\Page;
use Webauthn\PublicKeyCredentialSource;

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
