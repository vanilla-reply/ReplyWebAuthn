<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge;

use Reply\WebAuthn\Configuration\ConfigurationService;
use Reply\WebAuthn\Exception\UnsupportedAttestationStatementTypeException;
use Webauthn\AttestationStatement\AttestationStatementSupport;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;

class AttestationStatementSupportManagerConfigurator
{
    /**
     * @var ConfigurationService
     */
    private $configService;

    /**
     * @var AttestationStatementSupport[]|array
     */
    private $supportedTypes;

    /**
     * @param ConfigurationService $configService
     * @param AttestationStatementSupport[] $supportedTypes
     */
    public function __construct(ConfigurationService $configService, iterable $supportedTypes)
    {
        $this->configService = $configService;
        $this->supportedTypes = [];
        foreach ($supportedTypes as $supportedType) {
            $this->supportedTypes[$supportedType->name()] = $supportedType;
        }
    }

    public function __invoke(AttestationStatementSupportManager $manager): void
    {
        $config = $this->configService->get();
        foreach ($config->getAttestationStatementFormats() as $formatName) {
            $manager->add($this->getTypeByName($formatName));
        }
    }

    private function getTypeByName(string $name): AttestationStatementSupport
    {
        if (!isset($this->supportedTypes[$name])) {
            throw new UnsupportedAttestationStatementTypeException($name);
        }
        return $this->supportedTypes[$name];
    }
}
