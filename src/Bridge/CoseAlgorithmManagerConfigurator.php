<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge;

use Cose\Algorithm\Algorithm;
use Cose\Algorithm\Manager;
use Reply\WebAuthn\Configuration\ConfigurationReader;
use Reply\WebAuthn\Exception\UnsupportedAlgorithmException;

class CoseAlgorithmManagerConfigurator
{
    /**
     * @var ConfigurationReader
     */
    private $configurationReader;

    /**
     * @var Algorithm[]
     */
    private $supportedTypes;

    /**
     * @param ConfigurationReader $configurationReader
     * @param Algorithm[] $supportedTypes
     */
    public function __construct(ConfigurationReader $configurationReader, iterable $supportedTypes)
    {
        $this->configurationReader = $configurationReader;
        $this->supportedTypes = [];
        foreach ($supportedTypes as $supportedType) {
            $this->supportedTypes[get_class($supportedType)] = $supportedType;
        }
    }

    /**
     * @param Manager $manager
     */
    public function __invoke(Manager $manager): void
    {
        $config = $this->configurationReader->read();
        foreach ($config->getAlgorithms() as $className) {
            $manager->add($this->getByClassName($className));
        }
    }

    /**
     * @param string $className
     * @return Algorithm
     * @throws UnsupportedAlgorithmException
     */
    private function getByClassName(string $className): Algorithm
    {
        if (!isset($this->supportedTypes[$className])) {
            throw new UnsupportedAlgorithmException($className);
        }

        return $this->supportedTypes[$className];
    }
}
