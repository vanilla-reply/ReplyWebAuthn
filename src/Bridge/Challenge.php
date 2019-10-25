<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge;

class Challenge
{
    /**
     * @return string
     */
    public static function generate(): string
    {
        return random_bytes(32);
    }
}
