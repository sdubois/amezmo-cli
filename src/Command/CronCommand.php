<?php

declare(strict_types=1);

namespace AmezmoCli\Command;

use AmezmoCli\Api\CronApi;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class CronCommand extends AbstractCommand
{
    private const USAGE = 'Usage: cron list <instance-id>; cron get <instance-id> <cron-id>; cron create <instance-id> --name=NAME --environment=production|staging --expression=EXPR --command=SCRIPT|--command-file=FILE; or cron update <instance-id> <cron-id> [--command=SCRIPT|--command-file=FILE] [--expression=EXPR]';

    public function __construct(\AmezmoCli\Auth\CredentialStoreInterface $credentials, \AmezmoCli\Config\ConfigLoader $config, \AmezmoCli\Output\ResultRenderer $renderer)
    {
        parent::__construct($credentials, $config, $renderer);
        $this->setName('cron');
    }

    protected function configure(): void
    {
        parent::configure();
        $this->addArgument('action', InputArgument::REQUIRED, 'list, get, create, or update')
            ->addArgument('instance-id', InputArgument::REQUIRED)
            ->addArgument('cron-id', InputArgument::OPTIONAL)
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Permanent entry name: letters, numbers, and hyphens, up to 63 characters.')
            ->addOption('environment', null, InputOption::VALUE_REQUIRED, 'production or staging.')
            ->addOption('expression', null, InputOption::VALUE_REQUIRED, 'Five-field cron expression or an alias such as @daily.')
            ->addOption('command', null, InputOption::VALUE_REQUIRED, 'The bash script to run.')
            ->addOption('command-file', null, InputOption::VALUE_REQUIRED, 'Read the bash script from a file, or - for stdin.');
        $this->addDestructiveOption();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $action = $input->getArgument('action');
        $instanceId = (string) $input->getArgument('instance-id');
        $cronId = $input->getArgument('cron-id');
        try {
            if ($action === 'list') {
                $result = (new CronApi($this->client($input)))->list($instanceId);
                $columns = ['ID' => 'id', 'Name' => 'name', 'Environment' => 'environment_name', 'Expression' => 'expression', 'Alias' => 'expression_alias', 'Status' => 'status'];
            } elseif ($action === 'get' && is_string($cronId) && $cronId !== '') {
                $result = (new CronApi($this->client($input)))->get($instanceId, $cronId);
                $columns = [];
            } elseif ($action === 'create') {
                $name = $input->getOption('name');
                $environment = $input->getOption('environment');
                $expression = $input->getOption('expression');
                $command = $this->script($input);
                if (!is_string($name) || !is_string($environment) || !is_string($expression) || $command === null) {
                    return $this->fail($output, self::USAGE, 2);
                }
                if (preg_match('/^[A-Za-z0-9][A-Za-z0-9-]{0,62}$/', $name) !== 1) {
                    return $this->fail($output, 'Cron name must start with a letter or number, contain only letters, numbers, and hyphens, and be at most 63 characters.', 2);
                }
                if (!in_array($environment, ['production', 'staging'], true)) {
                    return $this->fail($output, 'Environment must be production or staging.', 2);
                }
                $this->confirmDestructive($input, $output, 'Create cron entry ' . $name . ' in ' . $environment . ' on instance ' . $instanceId . '? The name cannot be changed later.');
                $result = (new CronApi($this->client($input)))->create($instanceId, ['name' => $name, 'command' => $command, 'expression' => $expression, 'environment' => $environment]);
                $columns = [];
            } elseif ($action === 'update' && is_string($cronId) && $cronId !== '') {
                $fields = [];
                $command = $this->script($input);
                if ($command !== null) {
                    $fields['command'] = $command;
                }
                if (is_string($input->getOption('expression'))) {
                    $fields['expression'] = $input->getOption('expression');
                }
                if ($fields === []) {
                    return $this->fail($output, 'Provide --command, --command-file, or --expression to update.', 2);
                }
                $this->confirmDestructive($input, $output, 'Update cron entry ' . $cronId . ' on instance ' . $instanceId . '?');
                $result = (new CronApi($this->client($input)))->update($instanceId, $cronId, $fields);
                $columns = [];
            } else {
                return $this->fail($output, self::USAGE, 2);
            }
            $this->renderer->render($output, $result, $this->outputFormat($input), $columns);
            return 0;
        } catch (\Throwable $exception) {
            return $this->fail($output, $exception->getMessage(), $exception instanceof \InvalidArgumentException ? 2 : $this->exceptionCode($exception));
        }
    }

    /** Reads the script from --command or --command-file; null when neither is given. */
    private function script(InputInterface $input): ?string
    {
        $command = $input->getOption('command');
        $file = $input->getOption('command-file');
        if (is_string($command) && is_string($file)) {
            throw new \InvalidArgumentException('Use either --command or --command-file, not both.');
        }
        if (is_string($command)) {
            return $command;
        }
        if (!is_string($file)) {
            return null;
        }
        if ($file !== '-' && (!is_file($file) || !is_readable($file))) {
            throw new \InvalidArgumentException('Command file does not exist or is not readable.');
        }
        $contents = file_get_contents($file === '-' ? 'php://stdin' : $file);
        if ($contents === false) {
            throw new \InvalidArgumentException('Could not read the command file.');
        }
        return $contents;
    }
}
