<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge;

use Reply\WebAuthn\Configuration\ConfigurationReader;
use Reply\WebAuthn\Exception\UnsupportedAttestationStatementTypeException;
use Webauthn\AttestationStatement\AttestationStatementSupport;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;

class AttestationStatementSupportManagerConfigurator
{
    /**
     * @var ConfigurationReader
     */
    private $configurationReader;

    /**
     * @var AttestationStatementSupport[]|array
     */
    private $supportedTypes;

    /**
     * @param ConfigurationReader $configurationReader
     * @param AttestationStatementSupport[] $supportedTypes
     */
    public function __construct(ConfigurationReader $configurationReader, iterable $supportedTypes)
    {
        $this->configurationReader = $configurationReader;
        $this->supportedTypes = [];
        foreach ($supportedTypes as $supportedType) {
            $this->supportedTypes[$supportedType->name()] = $supportedType;
        }
    }

    /**
     * @param AttestationStatementSupportManager $manager
     */
    public function __invoke(AttestationStatementSupportManager $manager): void
    {
        $config = $this->configurationReader->read();
        foreach ($config->getAttestationStatementFormats() as $formatName) {
            $manager->add($this->getTypeByName($formatName));
        }
    }

    /**
     * @param string $name
     * @return AttestationStatementSupport
     * @throws UnsupportedAttestationStatementTypeException
     */
    private function getTypeByName(string $name): AttestationStatementSupport
    {
        if (!isset($this->supportedTypes[$name])) {
            throw new UnsupportedAttestationStatementTypeException($name);
        }
        return $this->supportedTypes[$name];
    }
}
