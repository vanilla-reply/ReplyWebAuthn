<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge;

class Challenge
{
    /** @var string */
    private $bytes;

    public function __construct()
    {
        $this->bytes = random_bytes(32);
    }

    /**
     * @return string
     */
    public function asBytes(): string
    {
        return $this->bytes;
    }
}
