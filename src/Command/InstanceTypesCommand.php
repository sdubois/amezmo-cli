<?php

declare(strict_types=1);

namespace AmezmoCli\Command;

use AmezmoCli\Api\InstanceTypeApi;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class InstanceTypesCommand extends AbstractCommand
{
    public function __construct(\AmezmoCli\Auth\CredentialStoreInterface $credentials, \AmezmoCli\Config\ConfigLoader $config, \AmezmoCli\Output\ResultRenderer $renderer)
    {
        parent::__construct($credentials, $config, $renderer);
        $this->setName('instance-types');
    }
    protected function configure(): void
    {
        parent::configure();
        $this->addArgument('action', InputArgument::REQUIRED, 'list');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getArgument('action') !== 'list') {
            return $this->fail($output, 'Usage: instance-types list', 2);
        }
        try {
            $this->renderer->render($output, (new InstanceTypeApi($this->client($input)))->list(), $this->outputFormat($input), ['ID' => 'id', 'Name' => 'name', 'Hourly' => 'hourly_price', 'Monthly' => 'monthly_price']);
            return 0;
        } catch (\Throwable $exception) {
            return $this->fail($output, $exception->getMessage(), $this->exceptionCode($exception));
        }
    }
}
