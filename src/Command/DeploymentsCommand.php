<?php

declare(strict_types=1);

namespace AmezmoCli\Command;

use AmezmoCli\Api\DeploymentApi;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class DeploymentsCommand extends AbstractCommand
{
    private const MAX_ARCHIVE_BYTES = 512 * 1024 * 1024;

    public function __construct(\AmezmoCli\Auth\CredentialStoreInterface $credentials, \AmezmoCli\Config\ConfigLoader $config, \AmezmoCli\Output\ResultRenderer $renderer)
    {
        parent::__construct($credentials, $config, $renderer);
        $this->setName('deployments');
    }
    protected function configure(): void
    {
        parent::configure();
        $this->addArgument('action', InputArgument::REQUIRED, 'get, create, or cancel')
            ->addArgument('instance-id', InputArgument::REQUIRED)
            ->addArgument('deployment-id', InputArgument::OPTIONAL)
            ->addOption('environment', null, InputOption::VALUE_REQUIRED)
            ->addOption('archive', null, InputOption::VALUE_REQUIRED)
            ->addOption('repo-owner', null, InputOption::VALUE_REQUIRED)
            ->addOption('repo-name', null, InputOption::VALUE_REQUIRED)
            ->addOption('branch', null, InputOption::VALUE_REQUIRED)
            ->addOption('pusher', null, InputOption::VALUE_REQUIRED)
            ->addOption('commit', null, InputOption::VALUE_REQUIRED)
            ->addOption('tag', null, InputOption::VALUE_REQUIRED)
            ->addOption('yes', null, InputOption::VALUE_NONE, 'Skip the confirmation prompt.');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $action = $input->getArgument('action');
        $instanceId = (string) $input->getArgument('instance-id');
        $deploymentId = $input->getArgument('deployment-id');
        try {
            $api = new DeploymentApi($this->client($input));
            if ($action === 'get' && is_string($deploymentId) && $deploymentId !== '') {
                $result = $api->get($instanceId, $deploymentId);
            } elseif ($action === 'cancel' && is_string($deploymentId) && $deploymentId !== '') {
                $this->confirmDestructive($input, $output, 'Cancel deployment ' . $deploymentId . ' on instance ' . $instanceId . '?');
                $result = $api->cancel($instanceId, $deploymentId);
            } elseif ($action === 'create' && is_string($input->getOption('environment')) && is_string($input->getOption('archive'))) {
                $archive = $input->getOption('archive');
                if (!is_file($archive) || !is_readable($archive)) {
                    return $this->fail($output, 'Archive file does not exist or is not readable.', 2);
                }
                if (filesize($archive) > self::MAX_ARCHIVE_BYTES) {
                    return $this->fail($output, 'Archive file exceeds the 512MB API limit.', 2);
                }
                if (is_string($input->getOption('repo-owner')) !== is_string($input->getOption('repo-name'))) {
                    return $this->fail($output, '--repo-owner and --repo-name must be given together.', 2);
                }
                $this->confirmDestructive($input, $output, 'Create a ' . $input->getOption('environment') . ' deployment on instance ' . $instanceId . '?');
                $fields = [];
                foreach (['repo-owner' => 'repo_owner', 'repo-name' => 'repo_name', 'branch' => 'branch', 'pusher' => 'pusher', 'commit' => 'commit', 'tag' => 'tag'] as $option => $field) {
                    if (is_string($input->getOption($option))) {
                        $fields[$field] = $input->getOption($option);
                    }
                }
                $result = $api->create($instanceId, $input->getOption('environment'), $archive, $fields);
            } else {
                return $this->fail($output, 'Usage: deployments get <instance-id> <deployment-id>; deployments cancel <instance-id> <deployment-id>; or deployments create <instance-id> --environment=NAME --archive=FILE', 2);
            }
            $this->renderer->render($output, $result, $this->outputFormat($input));
            return 0;
        } catch (\Throwable $exception) {
            return $this->fail($output, $exception->getMessage(), $this->exceptionCode($exception));
        }
    }
}
