<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge;

use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialSourceRepository;

interface CustomerCredentialRepository extends PublicKeyCredentialSourceRepository
{
    /**
     * @return PublicKeyCredentialSource[]
     */
    public function findAllByCustomerId(string $customerId): array;

    /**
     * @param string $credentialId
     */
    public function deleteById(string $credentialId): void;

    /**
     * @param string $customerId
     */
    public function deleteByCustomerId(string $customerId): void;
}
