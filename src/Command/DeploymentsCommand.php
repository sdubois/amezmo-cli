<?php

declare(strict_types=1);

namespace AmezmoCli\Command;

use AmezmoCli\Api\DeploymentApi;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class DeploymentsCommand extends AbstractCommand
{
    public function __construct(\AmezmoCli\Auth\CredentialStoreInterface $credentials, \AmezmoCli\Config\ConfigLoader $config, \AmezmoCli\Output\ResultRenderer $renderer)
    {
        parent::__construct($credentials, $config, $renderer);
        $this->setName('deployments');
    }
    protected function configure(): void
    {
        parent::configure();
        $this->addArgument('action', InputArgument::REQUIRED, 'get')->addArgument('instance-id', InputArgument::REQUIRED)->addArgument('deployment-id', InputArgument::REQUIRED);
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getArgument('action') !== 'get') {
            return $this->fail($output, 'Usage: deployments get <instance-id> <deployment-id>', 2);
        }
        try {
            $result = (new DeploymentApi($this->client($input)))->get((string) $input->getArgument('instance-id'), (string) $input->getArgument('deployment-id'));
            $this->renderer->render($output, $result, $this->outputFormat($input));
            return 0;
        } catch (\Throwable $exception) {
            return $this->fail($output, $exception->getMessage(), $this->exceptionCode($exception));
        }
    }
}
