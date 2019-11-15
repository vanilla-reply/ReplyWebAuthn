<?php declare(strict_types=1);

namespace ReplyWebAuthnTest;

use PHPUnit\Framework\TestCase;
use Reply\WebAuthn\Bridge\PublicKeyCredentialDescriptorFakeFactory;
use Webauthn\PublicKeyCredentialDescriptor;

class PublicKeyCredentialDescriptorFakeFactoryTest extends TestCase
{
    public function testCreatesReproducibleIdentifiers(): void
    {
        putenv('APP_SECRET=' . bin2hex(random_bytes(16)));
        $subject = new PublicKeyCredentialDescriptorFakeFactory();
        $username = 'foobar';

        /** @var PublicKeyCredentialDescriptor|null $previous */
        $previous = null;
        for ($i = 0; $i < 5; $i++) {
            $result = $subject->create($username);
            if ($previous !== null) {
                self::assertSame($previous->getId(), $result->getId());
            }
            $previous = $result;
        }
    }
}
