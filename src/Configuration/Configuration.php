<?php declare(strict_types=1);

namespace Reply\WebAuthn\Configuration;

use Cose\Algorithm\Signature\ECDSA\ES256;
use Cose\Algorithm\Signature\ECDSA\ES384;
use Cose\Algorithm\Signature\ECDSA\ES512;
use Cose\Algorithm\Signature\RSA\RS256;
use Cose\Algorithm\Signature\RSA\RS384;
use Cose\Algorithm\Signature\RSA\RS512;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialCreationOptions;

class Configuration implements \IteratorAggregate
{
    /**
     * @var array
     */
    private $values;

    public function __construct(array $values)
    {
        $this->values = array_merge(self::getDefaults(), $values);
    }

    private static function getDefaults(): array
    {
        return [
            'timeout' => 20,
            'attestation' => PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_DIRECT,
            'userVerification' => AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_PREFERRED,
            'requireResidentKey' => false,
            'attestationStatementFormats' => [
                'android-key', 'fido-u2f', 'none', 'tpm'
            ],
            'algorithms' => [
                ES256::class,
                ES384::class,
                ES512::class,
                RS256::class,
                RS384::class,
                RS512::class
            ]
        ];
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

    public function getAlgorithms(): array
    {
        return $this->values['algorithms'];
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
