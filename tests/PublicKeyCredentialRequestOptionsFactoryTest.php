<?php declare(strict_types=1);

namespace ReplyWebAuthnTest;

use PHPUnit\Framework\TestCase;
use Reply\WebAuthn\Bridge\PublicKeyCredentialRequestOptionsFactory;
use Reply\WebAuthn\Configuration\Configuration;
use Reply\WebAuthn\Configuration\ConfigurationReader;

class PublicKeyCredentialRequestOptionsFactoryTest extends TestCase
{
    /**
     * @var PublicKeyCredentialRequestOptionsFactory
     */
    private $subject;

    /**
     * @var Configuration
     */
    private $config;

    protected function setUp(): void
    {
        $this->config = new Configuration([]);

        $configReaderMock = $this
            ->createMock(ConfigurationReader::class);

        $configReaderMock->expects($this->any())
            ->method('read')
            ->will($this->returnValue($this->config));

        $this->subject = new PublicKeyCredentialRequestOptionsFactory($configReaderMock);
    }

    public function testRequestOptionsFollowConfig(): void
    {
        $hostname = 'example.com';
        $requestOptions = $this->subject->create($hostname, []);

        self::assertSame($this->config->getTimeout() * 1000, $requestOptions->getTimeout());
        self::assertSame($this->config->getUserVerification(), $requestOptions->getUserVerification());
        self::assertSame($hostname, $requestOptions->getRpId());
    }
}
