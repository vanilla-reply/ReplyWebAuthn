<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge;

use Reply\WebAuthn\Exception\InvalidConfigurationException;
use Webauthn\PublicKeyCredentialDescriptor;

class PublicKeyCredentialDescriptorFakeFactory
{
    /**
     * @param string $username
     * @return PublicKeyCredentialDescriptor
     * @throws InvalidConfigurationException
     */
    public function create(string $username): PublicKeyCredentialDescriptor
    {
        $salt = getenv('APP_SECRET');
        if (!is_string($salt) || empty($salt)) {
            throw new InvalidConfigurationException('Invalid value for environment variable APP_SECRET');
        }

        $hash = hash('sha512', $salt . $username);

        return new PublicKeyCredentialDescriptor(
            PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
            hex2bin($hash)
        );
    }
}
