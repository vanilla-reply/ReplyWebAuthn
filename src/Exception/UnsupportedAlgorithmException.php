<?php declare(strict_types=1);

namespace Reply\WebAuthn\Exception;

use Shopware\Core\Framework\ShopwareHttpException;

class UnsupportedAlgorithmException extends ShopwareHttpException
{
    /**
     * @param string $class
     */
    public function __construct(string $class)
    {
        parent::__construct('{{ class }} is not a supported algorithm', ['class' => $class]);
    }

    /**
     * @return string
     */
    public function getErrorCode(): string
    {
        return 'WEBAUTHN__UNSUPPORTED_ALGORITHM';
    }
}
