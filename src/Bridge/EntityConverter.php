<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge;

use Shopware\Core\Checkout\Customer\CustomerEntity;
use Webauthn\PublicKeyCredentialUserEntity;

class EntityConverter
{
    /**
     * @param CustomerEntity $customerEntity
     * @return PublicKeyCredentialUserEntity
     */
    public static function toUserEntity(CustomerEntity $customerEntity): PublicKeyCredentialUserEntity
    {
        $name = $customerEntity->getEmail();

        return new PublicKeyCredentialUserEntity(
            $name,
            hex2bin($customerEntity->getId()),
            $name
        );
    }
}
