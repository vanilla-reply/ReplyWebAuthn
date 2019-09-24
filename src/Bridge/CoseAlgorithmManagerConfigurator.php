<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge;

use Cose\Algorithm\Algorithm;
use Cose\Algorithm\Manager;
use Cose\Algorithm\Signature\ECDSA\ES256;
use Cose\Algorithm\Signature\ECDSA\ES256K;
use Cose\Algorithm\Signature\ECDSA\ES384;
use Cose\Algorithm\Signature\ECDSA\ES512;
use Cose\Algorithm\Signature\RSA\RS1;
use Cose\Algorithm\Signature\RSA\RS256;
use Cose\Algorithm\Signature\RSA\RS384;
use Cose\Algorithm\Signature\RSA\RS512;

class CoseAlgorithmManagerConfigurator
{
    public function __invoke(Manager $manager)
    {
        // TODO: Make algorithms configurable
        foreach ($this->getSupportedAlgorithms() as $algorithm) {
            $manager->add($algorithm);
        }
    }

    /**
     * @return Algorithm[]
     */
    private function getSupportedAlgorithms(): array
    {
        return [
            new ES256(),
            new ES256K(),
            new ES384(),
            new ES512(),
            new RS1(),
            new RS256(),
            new RS384(),
            new RS512()
        ];
    }
}
