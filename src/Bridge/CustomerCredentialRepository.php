<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge;

use Webauthn\PublicKeyCredentialSource;

interface CustomerCredentialRepository extends \Webauthn\PublicKeyCredentialSourceRepository
{
    /**
     * @return PublicKeyCredentialSource[]
     */
    public function findAllByCustomerId(string $customerId): array;

    public function deleteById(string $credentialId): void;

    public function deleteByCustomerId(string $customerId): void;
}
