<?php declare(strict_types=1);

namespace Reply\WebAuthn\Exception;

use Shopware\Core\Framework\ShopwareHttpException;

class CredentialRegistrationFailedException extends ShopwareHttpException
{
    /**
     * @return string
     */
    public function getErrorCode(): string
    {
        return 'WEBAUTHN__CREDENTIAL_REGISTRATION_FAILED';
    }
}
