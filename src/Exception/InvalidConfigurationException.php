<?php declare(strict_types=1);

namespace Reply\WebAuthn\Exception;

use Shopware\Core\Framework\ShopwareHttpException;

class InvalidConfigurationException extends ShopwareHttpException
{
    public function getErrorCode(): string
    {
        return 'WEBAUTHN__INVALID_CONFIGURATION';
    }
}
