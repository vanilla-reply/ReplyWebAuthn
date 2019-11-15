<?php declare(strict_types=1);

namespace ReplyWebAuthnTest;

use PHPUnit\Framework\TestCase;
use Reply\WebAuthn\Bridge\PublicKeyCredentialDescriptorFakeFactory;
use Webauthn\PublicKeyCredentialDescriptor;

class PublicKeyCredentialDescriptorFakeFactoryTest extends TestCase
{
    /**
     * @var PublicKeyCredentialDescriptorFakeFactory
     */
    private $subject;

    protected function setUp(): void
    {
        putenv('APP_SECRET=' . bin2hex(random_bytes(16)));
        $this->subject = new PublicKeyCredentialDescriptorFakeFactory();
    }

    public function testReturnsSameIdentifierForSameUsername(): void
    {
        $username = 'foobar';

        /** @var PublicKeyCredentialDescriptor|null $previous */
        $previous = null;
        for ($i = 0; $i < 5; $i++) {
            $result = $this->subject->create($username);
            if ($previous !== null) {
                self::assertSame($previous->getId(), $result->getId());
            }
            $previous = $result;
        }
        self::assertNotNull($previous);
    }

    public function testReturnsDifferentIdentifierForDifferentUsername(): void
    {
        /** @var PublicKeyCredentialDescriptor|null $previous */
        $previous = null;
        for ($i = 0; $i < 5; $i++) {
            $username = 'bar' . $i;
            $result = $this->subject->create($username);
            if ($previous !== null) {
                self::assertNotEquals($previous->getId(), $result->getId());
            }
            $previous = $result;
        }
        self::assertNotNull($previous);
    }
}
