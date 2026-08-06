<?php

declare(strict_types=1);

namespace AmezmoCli\Command;

use AmezmoCli\Api\InstanceApi;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class InstancesCommand extends AbstractCommand
{
    public function __construct(\AmezmoCli\Auth\CredentialStoreInterface $credentials, \AmezmoCli\Config\ConfigLoader $config, \AmezmoCli\Output\ResultRenderer $renderer)
    {
        parent::__construct($credentials, $config, $renderer);
        $this->setName('instances');
    }
    protected function configure(): void
    {
        parent::configure();
        $this->addArgument('action', InputArgument::REQUIRED, 'list or get')->addArgument('instance-id', InputArgument::OPTIONAL)->addOption('instance-type', null, InputOption::VALUE_REQUIRED, 'Filter by instance type.');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $api = new InstanceApi($this->client($input));
        $action = $input->getArgument('action');
        $id = $input->getArgument('instance-id');
        try {
            if ($action === 'list') {
                $result = $api->list($input->getOption('instance-type'));
                $columns = ['ID' => 'id', 'Name' => 'name', 'Type' => 'instance_type', 'Region' => 'region', 'State' => 'state'];
            } elseif ($action === 'get' && is_string($id) && $id !== '') {
                $result = $api->get($id);
                $columns = [];
            } else {
                return $this->fail($output, 'Usage: instances list [--instance-type=TYPE] or instances get <instance-id>', 2);
            }
            $this->renderer->render($output, $result, $this->outputFormat($input), $columns);
            return 0;
        } catch (\Throwable $exception) {
            return $this->fail($output, $exception->getMessage(), $this->exceptionCode($exception));
        }
    }
}
