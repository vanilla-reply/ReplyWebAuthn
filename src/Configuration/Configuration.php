<?php declare(strict_types=1);

namespace Reply\WebAuthn\Configuration;

use ArrayIterator;
use Iterator;
use IteratorAggregate;

class Configuration implements IteratorAggregate
{
    /**
     * @var array
     */
    private $values;

    /**
     * @param array $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * @return string
     */
    public function getAttestation(): string
    {
        return $this->values['attestation'];
    }

    /**
     * @return string
     */
    public function getUserVerification(): string
    {
        return $this->values['userVerification'];
    }

    /**
     * @return array
     */
    public function getAttestationStatementFormats(): array
    {
        return $this->values['attestationStatementFormats'];
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->values['timeout'];
    }

    /**
     * @return array
     */
    public function getAlgorithms(): array
    {
        return $this->values['algorithms'];
    }

    /**
     * @return bool
     */
    public function isResidentKeyRequired(): bool
    {
        return $this->values['requireResidentKey'];
    }

    /**
     * @return Iterator
     */
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->values);
    }
}
