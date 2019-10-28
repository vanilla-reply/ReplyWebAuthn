<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge;

use Webauthn\PublicKeyCredentialSourceRepository;
use Webauthn\PublicKeyCredentialUserEntity;

interface CustomerCredentialRepository extends PublicKeyCredentialSourceRepository
{
    /**
     * @return PublicKeyCredentialEntity[]
     */
    public function findAllForUserEntity(PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity): array;

    /**
     * @param string $credentialId
     */
    public function deleteById(string $credentialId): void;

    /**
     * @param PublicKeyCredentialUserEntity $userEntity
     */
    public function deleteAllForUserEntity(PublicKeyCredentialUserEntity $userEntity): void;
}
