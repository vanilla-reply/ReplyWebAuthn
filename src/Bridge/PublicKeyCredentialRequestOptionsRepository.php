<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge;

use Webauthn\PublicKeyCredentialRequestOptions;

class PublicKeyCredentialRequestOptionsRepository
{
    public function save(string $userHandle, PublicKeyCredentialRequestOptions $requestOptions): void
    {
        // TODO: Implement
    }

    public function fetch(string $userHandle): ?PublicKeyCredentialRequestOptions
    {
        // TODO: Implement
    }
}
