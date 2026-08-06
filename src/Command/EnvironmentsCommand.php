<?php

declare(strict_types=1);

namespace AmezmoCli\Command;

use AmezmoCli\Api\EnvironmentApi;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class EnvironmentsCommand extends AbstractCommand
{
    public function __construct(\AmezmoCli\Auth\CredentialStoreInterface $credentials, \AmezmoCli\Config\ConfigLoader $config, \AmezmoCli\Output\ResultRenderer $renderer)
    {
        parent::__construct($credentials, $config, $renderer);
        $this->setName('environments');
    }
    protected function configure(): void
    {
        parent::configure();
        $this->addArgument('action', InputArgument::REQUIRED, 'list or get')->addArgument('instance-id', InputArgument::REQUIRED)->addArgument('environment-name', InputArgument::OPTIONAL);
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $action = $input->getArgument('action');
        $instanceId = (string) $input->getArgument('instance-id');
        $name = $input->getArgument('environment-name');
        try {
            if ($action === 'list') {
                $result = (new EnvironmentApi($this->client($input)))->list($instanceId);
                $columns = ['ID' => 'id', 'Name' => 'name', 'State' => 'state', 'Domain' => 'app_domain'];
            } elseif ($action === 'get' && is_string($name) && $name !== '') {
                $result = (new EnvironmentApi($this->client($input)))->get($instanceId, $name);
                $columns = [];
            } else {
                return $this->fail($output, 'Usage: environments list <instance-id> or environments get <instance-id> <environment-name>', 2);
            }
            $this->renderer->render($output, $result, $this->outputFormat($input), $columns);
            return 0;
        } catch (\Throwable $exception) {
            return $this->fail($output, $exception->getMessage(), $this->exceptionCode($exception));
        }
    }
}
