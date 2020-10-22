<?php declare(strict_types=1);

namespace Reply\WebAuthn\DataAbstractionLayer\Entity;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                                 add(WebauthnCredentialEntity $entity)
 * @method void                                 set(string $key, WebauthnCredentialEntity $entity)
 * @method \Generator<WebauthnCredentialEntity> getIterator()
 * @method WebauthnCredentialEntity[]           getElements()
 * @method WebauthnCredentialEntity|null        get(string $key)
 * @method WebauthnCredentialEntity|null        first()
 * @method WebauthnCredentialEntity|null        last()
 */
class WebauthnCredentialCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return WebauthnCredentialEntity::class;
    }
}
