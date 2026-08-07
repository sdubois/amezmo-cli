<?php

declare(strict_types=1);

namespace AmezmoCli\Command;

use AmezmoCli\Api\EnvironmentApi;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
        $this->addArgument('action', InputArgument::REQUIRED, 'list, get, or update')
            ->addArgument('instance-id', InputArgument::REQUIRED)
            ->addArgument('environment-name', InputArgument::OPTIONAL)
            ->addOption('auto-deploy-tag-pattern', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY)
            ->addOption('auto-deploy-branch-pattern', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY)
            ->addOption('newrelic-license-key', null, InputOption::VALUE_REQUIRED)
            ->addOption('ssh-enabled', null, InputOption::VALUE_REQUIRED)
            ->addOption('trusted-ssh-ip', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY);
        $this->addDestructiveOption();
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
            } elseif ($action === 'update' && is_string($name) && $name !== '') {
                $fields = [];
                if ($input->getOption('auto-deploy-tag-pattern') !== []) {
                    $fields['auto_deploy_tag_patterns'] = $input->getOption('auto-deploy-tag-pattern');
                }
                if ($input->getOption('auto-deploy-branch-pattern') !== []) {
                    $fields['auto_deploy_branch_patterns'] = $input->getOption('auto-deploy-branch-pattern');
                }
                foreach ([
                    'newrelic-license-key' => 'newrelic_license_key',
                    'ssh-enabled' => 'ssh_enabled',
                ] as $option => $field) {
                    $value = $input->getOption($option);
                    if ($value !== null) {
                        $fields[$field] = $value === 'true' || $value === '1' ? true : ($value === 'false' || $value === '0' ? false : ($value === 'null' ? null : $value));
                    }
                }
                if ($input->getOption('trusted-ssh-ip') !== []) {
                    $fields['trusted_ssh_ips'] = $input->getOption('trusted-ssh-ip');
                }
                if ($fields === []) {
                    return $this->fail($output, 'Provide at least one documented environment setting to update.', 2);
                }
                $this->confirmDestructive($input, $output, 'Update environment ' . $name . '?');
                $result = (new EnvironmentApi($this->client($input)))->update($instanceId, $name, $fields);
                $columns = [];
            } else {
                return $this->fail($output, 'Usage: environments list|get|update <instance-id> <environment-name>', 2);
            }
            $this->renderer->render($output, $result, $this->outputFormat($input), $columns);
            return 0;
        } catch (\Throwable $exception) {
            return $this->fail($output, $exception->getMessage(), $this->exceptionCode($exception));
        }
    }
}
