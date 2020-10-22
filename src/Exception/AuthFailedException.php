<?php declare(strict_types=1);

namespace Reply\WebAuthn\Exception;

use Shopware\Core\Framework\ShopwareHttpException;
use Throwable;

class AuthFailedException extends ShopwareHttpException
{
    public function __construct(string $message = 'Authentication failed', array $parameters = [], ?Throwable $e = null)
    {
        parent::__construct($message, $parameters, $e);
    }

    /**
     * @return string
     */
    public function getErrorCode(): string
    {
        return 'WEBAUTHN__AUTH_FAILED';
    }
}
