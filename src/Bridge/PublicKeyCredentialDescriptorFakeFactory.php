<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge;

use Reply\WebAuthn\Exception\InvalidConfigurationException;
use Webauthn\PublicKeyCredentialDescriptor;

class PublicKeyCredentialDescriptorFakeFactory
{
    /** @var string */
    private $secret;

    /**
     * @param string $secret
     */
    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    /**
     * @param string $username
     * @return PublicKeyCredentialDescriptor
     * @throws InvalidConfigurationException
     */
    public function create(string $username): PublicKeyCredentialDescriptor
    {
        $salt = $this->secret;
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
