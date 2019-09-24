<?php declare(strict_types=1);

namespace Reply\WebAuthn\Configuration;

use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialCreationOptions;

class Configuration implements \IteratorAggregate
{
    /**
     * @var array
     */
    private $values;

    private static $defaults = [
        'timeout' => 20000,
        'attestation' => PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_DIRECT,
        'userVerification' => AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_PREFERRED,
        'requireResidentKey' => false,
        'attestationStatementFormats' => ['android-key', 'fido-u2f', 'none', 'tpm']
    ];

    public function __construct(array $values)
    {
        $this->values = array_merge(self::$defaults, $values);
    }

    public function getAttestation(): string
    {
        return $this->values['attestation'];
    }

    public function getUserVerification(): string
    {
        return $this->values['userVerification'];
    }

    public function getAttestationStatementFormats(): array
    {
        return $this->values['attestationStatementFormats'];
    }

    public function getTimeout(): int
    {
        return $this->values['timeout'];
    }

    public function isResidentKeyRequired(): bool
    {
        return $this->values['requireResidentKey'];
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->values);
    }
}
