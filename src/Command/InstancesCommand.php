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
        $this->addArgument('action', InputArgument::REQUIRED, 'list, get, create, or terminate')
            ->addArgument('instance-id', InputArgument::OPTIONAL)
            ->addOption('instance-type', null, InputOption::VALUE_REQUIRED, 'Filter by instance type.')
            ->addOption('runtime', null, InputOption::VALUE_REQUIRED, 'Instance runtime (php).')
            ->addOption('region', null, InputOption::VALUE_REQUIRED, 'Region ID.')
            ->addOption('name', null, InputOption::VALUE_REQUIRED)
            ->addOption('domain', null, InputOption::VALUE_REQUIRED)
            ->addOption('app-type', null, InputOption::VALUE_REQUIRED)
            ->addOption('php-version', null, InputOption::VALUE_REQUIRED)
            ->addOption('composer-version', null, InputOption::VALUE_REQUIRED)
            ->addOption('mysql-version', null, InputOption::VALUE_REQUIRED)
            ->addOption('mysql-enabled', null, InputOption::VALUE_REQUIRED)
            ->addOption('mysql-database-name', null, InputOption::VALUE_REQUIRED)
            ->addOption('mysql-database-user', null, InputOption::VALUE_REQUIRED)
            ->addOption('mysql-database-password', null, InputOption::VALUE_REQUIRED);
        $this->addDestructiveOption();
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
            } elseif ($action === 'create') {
                $runtime = $input->getOption('runtime');
                $instanceType = $input->getOption('instance-type');
                $region = $input->getOption('region');
                if (!is_string($runtime) || !is_string($instanceType) || !is_string($region)) {
                    return $this->fail($output, 'Usage: instances create --runtime=php --instance-type=TYPE --region=REGION', 2);
                }
                $fields = ['runtime' => $runtime, 'instance_type' => $instanceType, 'region' => $region];
                foreach ([
                    'name' => 'name', 'domain' => 'domain', 'app-type' => 'app_type',
                    'php-version' => 'php[version]', 'composer-version' => 'php[composer_version]',
                    'mysql-version' => 'mysql[version]', 'mysql-enabled' => 'mysql[enabled]',
                    'mysql-database-name' => 'mysql[database][name]', 'mysql-database-user' => 'mysql[database][user]',
                    'mysql-database-password' => 'mysql[database][password]',
                ] as $option => $field) {
                    $value = $input->getOption($option);
                    if ($value !== null) {
                        $fields[$field] = $value;
                    }
                }
                $this->confirmDestructive($input, $output, 'Create a new Amezmo instance?');
                $result = $api->create($fields);
                $columns = [];
            } elseif ($action === 'terminate' && is_string($id) && $id !== '') {
                $this->confirmDestructive($input, $output, 'Terminate instance ' . $id . '? This cannot be undone.');
                $result = $api->terminate($id);
                $columns = [];
            } else {
                return $this->fail($output, 'Usage: instances list, get <id>, create --runtime=php --instance-type=TYPE --region=REGION, or terminate <id> --yes', 2);
            }
            $this->renderer->render($output, $result, $this->outputFormat($input), $columns);
            return 0;
        } catch (\Throwable $exception) {
            return $this->fail($output, $exception->getMessage(), $this->exceptionCode($exception));
        }
    }
}
