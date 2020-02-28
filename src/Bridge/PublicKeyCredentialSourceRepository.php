<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge;

use Webauthn\PublicKeyCredentialSourceRepository as BaseRepository;
use Webauthn\PublicKeyCredentialUserEntity;

interface PublicKeyCredentialSourceRepository extends BaseRepository
{
    /**
     * @return PublicKeyCredentialSource[]
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
