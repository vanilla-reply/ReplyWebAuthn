<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge;

use DateTimeImmutable;
use Webauthn\PublicKeyCredentialSource;

class PublicKeyCredentialEntity extends PublicKeyCredentialSource
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var DateTimeImmutable|null
     */
    protected $createdAt;

    /**
     * @var DateTimeImmutable|null
     */
    protected $updatedAt;

    /**
     * @param PublicKeyCredentialSource $publicKeyCredentialSource
     */
    public function __construct(PublicKeyCredentialSource $publicKeyCredentialSource)
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
        $this->name = '';
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

    /**
     * @return DateTimeImmutable|null
     */
    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @param DateTimeImmutable $createdAt
     */
    public function setCreatedAt(DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTimeImmutable|null $updatedAt
     */
    public function setUpdatedAt(?DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        $values = parent::jsonSerialize();
        $values['name'] = $this->name;
        if ($this->createdAt !== null) {
            $values['createdAt'] = $this->createdAt->format(DATE_ATOM);
        }
        if ($this->updatedAt !== null) {
            $values['updatedAt'] = $this->updatedAt->format(DATE_ATOM);
        }

        return $values;
    }
}
