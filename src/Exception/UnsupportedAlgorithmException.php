<?php declare(strict_types=1);

namespace Reply\WebAuthn\Exception;

use Shopware\Core\Framework\ShopwareHttpException;

class UnsupportedAlgorithmException extends ShopwareHttpException
{
    public function __construct(string $class)
    {
        parent::__construct('{{ class }} is not a supported algorithm', ['class' => $class]);
    }

    public function getErrorCode(): string
    {
        return 'WEBAUTHN__UNSUPPORTED_ALGORITHM';
    }
}
