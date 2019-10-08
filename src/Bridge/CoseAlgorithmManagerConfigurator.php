<?php declare(strict_types=1);

namespace Reply\WebAuthn\Bridge;

use Cose\Algorithm\Algorithm;
use Cose\Algorithm\Manager;
use Reply\WebAuthn\Configuration\ConfigurationService;
use Reply\WebAuthn\Exception\UnsupportedAlgorithmException;

class CoseAlgorithmManagerConfigurator
{
    /**
     * @var ConfigurationService
     */
    private $configurationService;

    /**
     * @var Algorithm[]
     */
    private $supportedTypes;

    /**
     * @param ConfigurationService $configurationService
     * @param Algorithm[] $supportedTypes
     */
    public function __construct(ConfigurationService $configurationService, iterable $supportedTypes)
    {
        $this->configurationService = $configurationService;
        $this->supportedTypes = [];
        foreach ($supportedTypes as $supportedType) {
            $this->supportedTypes[get_class($supportedType)] = $supportedType;
        }
    }

    /**
     * @param Manager $manager
     */
    public function __invoke(Manager $manager)
    {
        $config = $this->configurationService->get();
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
