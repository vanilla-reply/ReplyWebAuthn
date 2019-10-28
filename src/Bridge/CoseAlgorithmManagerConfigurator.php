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
            $className = get_class($supportedType);
            $configName = strtolower(substr($className, strrpos($className, '\\') + 1));
            $this->supportedTypes[$configName] = $supportedType;
        }
    }

    /**
     * @param Manager $manager
     */
    public function __invoke(Manager $manager): void
    {
        $config = $this->configurationReader->read();
        foreach ($config->getAlgorithms() as $configName) {
            $manager->add($this->getByConfigName($configName));
        }
    }

    /**
     * @param string $configName
     * @return Algorithm
     * @throws UnsupportedAlgorithmException
     */
    private function getByConfigName(string $configName): Algorithm
    {
        if (!isset($this->supportedTypes[$configName])) {
            throw new UnsupportedAlgorithmException($configName);
        }

        return $this->supportedTypes[$configName];
    }
}
