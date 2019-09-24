<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge;

use Webauthn\PublicKeyCredentialSource;

class NamedCredential extends PublicKeyCredentialSource
{
    /**
     * @var string
     */
    protected $name;

    public function __construct(PublicKeyCredentialSource $publicKeyCredentialSource, string $name)
    {
        parent::__construct(
            $publicKeyCredentialSource->getPublicKeyCredentialId(),
            $publicKeyCredentialSource->getType(),
            $publicKeyCredentialSource->getTransports(),
            $publicKeyCredentialSource->getAttestationType(),
            $publicKeyCredentialSource->getTrustPath(),
            $publicKeyCredentialSource->getAaguid(),
            $publicKeyCredentialSource->getCredentialPublicKey(),
            $publicKeyCredentialSource->getUserHandle(),
            $publicKeyCredentialSource->getCounter()
        );
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function jsonSerialize(): array
    {
        $values = parent::jsonSerialize();
        $values['name'] = $this->name;

        return $values;
    }
}
