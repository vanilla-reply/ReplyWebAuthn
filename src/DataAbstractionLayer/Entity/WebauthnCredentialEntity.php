<?php

namespace Reply\WebAuthn\DataAbstractionLayer\Entity;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;

class WebauthnCredentialEntity extends Entity
{
    /** @var string binary */
    protected $externalId;

    /** @var string binary */
    protected $userHandle;

    /** @var string */
    protected $name;

    /** @var string */
    protected $type;

    /** @var string json */
    protected $transports;

    /** @var string */
    protected $attestationType;

    /** @var string json */
    protected $trustPath;

    /** @var string */
    protected $aaguid;

    /** @var string binary */
    protected $publicKey;

    /** @var int */
    protected $counter;

    /**
     * @return string
     */
    public function getExternalId(): string
    {
        return $this->externalId;
    }

    /**
     * @param string $externalId
     */
    public function setExternalId(string $externalId): void
    {
        $this->externalId = $externalId;
    }

    /**
     * @return string
     */
    public function getUserHandle(): string
    {
        return $this->userHandle;
    }

    /**
     * @param string $userHandle
     */
    public function setUserHandle(string $userHandle): void
    {
        $this->userHandle = $userHandle;
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
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getTransports(): string
    {
        return $this->transports;
    }

    /**
     * @param string $transports
     */
    public function setTransports(string $transports): void
    {
        $this->transports = $transports;
    }

    /**
     * @return string
     */
    public function getAttestationType(): string
    {
        return $this->attestationType;
    }

    /**
     * @param string $attestationType
     */
    public function setAttestationType(string $attestationType): void
    {
        $this->attestationType = $attestationType;
    }

    /**
     * @return string
     */
    public function getTrustPath(): string
    {
        return $this->trustPath;
    }

    /**
     * @param string $trustPath
     */
    public function setTrustPath(string $trustPath): void
    {
        $this->trustPath = $trustPath;
    }

    /**
     * @return string
     */
    public function getAaguid(): string
    {
        return $this->aaguid;
    }

    /**
     * @param string $aaguid
     */
    public function setAaguid(string $aaguid): void
    {
        $this->aaguid = $aaguid;
    }

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * @param string $publicKey
     */
    public function setPublicKey(string $publicKey): void
    {
        $this->publicKey = $publicKey;
    }

    /**
     * @return int
     */
    public function getCounter(): int
    {
        return $this->counter;
    }

    /**
     * @param int $counter
     */
    public function setCounter(int $counter): void
    {
        $this->counter = $counter;
    }
}
