<?php declare(strict_types=1);

namespace Reply\WebAuthn\Console;

use Reply\WebAuthn\Bridge\PublicKeyCredentialCreationOptionsRepository;
use Reply\WebAuthn\Bridge\PublicKeyCredentialRequestOptionsRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanupCommand extends Command
{
    protected static $defaultName = 'reply:webauthn:cleanup';

    /**
     * @var PublicKeyCredentialCreationOptionsRepository
     */
    private $creationOptionRepository;

    /**
     * @var PublicKeyCredentialRequestOptionsRepository
     */
    private $requestOptionsRepository;

    public function __construct(PublicKeyCredentialCreationOptionsRepository $creationOptionRepository, PublicKeyCredentialRequestOptionsRepository $requestOptionsRepository)
    {
        parent::__construct();
        $this->creationOptionRepository = $creationOptionRepository;
        $this->requestOptionsRepository = $requestOptionsRepository;
    }

    protected function configure(): void
    {
        $this->setDescription('Cleanup temporary data related to login and registration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->creationOptionRepository->cleanup();
        $this->requestOptionsRepository->cleanup();

        return 0;
    }
}
