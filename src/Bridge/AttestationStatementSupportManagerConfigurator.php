<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge;

use Reply\WebAuthn\Configuration\ConfigurationService;
use Webauthn\AttestationStatement\AndroidKeyAttestationStatementSupport;
use Webauthn\AttestationStatement\AttestationStatementSupport;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\FidoU2FAttestationStatementSupport;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AttestationStatement\TPMAttestationStatementSupport;

class AttestationStatementSupportManagerConfigurator
{
    /**
     * @var ConfigurationService
     */
    private $configService;

    public function __construct(ConfigurationService $configService)
    {
        $this->configService = $configService;
    }

    public function __invoke(AttestationStatementSupportManager $manager)
    {
        $config = $this->configService->get();

        foreach ($this->getSupportedFormats() as $supportedFormat) {
            if (in_array($supportedFormat->name(), $config->getAttestationStatementFormats())) {
                $manager->add($supportedFormat);
            }
        }
    }

    /**
     * @return AttestationStatementSupport[]
     */
    private function getSupportedFormats(): array
    {
        return [
            new FidoU2FAttestationStatementSupport(),
            new NoneAttestationStatementSupport(),
            new AndroidKeyAttestationStatementSupport(),
            new TPMAttestationStatementSupport()
        ];
    }
}
