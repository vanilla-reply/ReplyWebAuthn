<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge;

use Webauthn\PublicKeyCredentialCreationOptions;

class PublicKeyCredentialCreationOptionsRepository
{
    public function save(PublicKeyCredentialCreationOptions $creationOptions): void
    {
        // TODO: Implement
    }

    public function fetch(string $userHandle): ?PublicKeyCredentialCreationOptions
    {
        // TODO: Implement
    }
}
