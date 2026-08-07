<?php

declare(strict_types=1);

namespace AmezmoCli\Command;

use AmezmoCli\Api\WorkerApi;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class WorkersCommand extends AbstractCommand
{
    public function __construct(\AmezmoCli\Auth\CredentialStoreInterface $credentials, \AmezmoCli\Config\ConfigLoader $config, \AmezmoCli\Output\ResultRenderer $renderer)
    {
        parent::__construct($credentials, $config, $renderer);
        $this->setName('workers');
    }

    protected function configure(): void
    {
        parent::configure();
        $this->addArgument('action', InputArgument::REQUIRED, 'get, start, stop, or restart')
            ->addArgument('instance-id', InputArgument::REQUIRED)
            ->addArgument('worker-id', InputArgument::REQUIRED);
        $this->addDestructiveOption();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $action = (string) $input->getArgument('action');
        if (!in_array($action, ['get', 'start', 'stop', 'restart'], true)) {
            return $this->fail($output, 'Usage: workers get|start|stop|restart <instance-id> <worker-id>', 2);
        }

        try {
            if ($action !== 'get') {
                $this->confirmDestructive($input, $output, ucfirst($action) . ' worker ' . $input->getArgument('worker-id') . '?');
            }
            $api = new WorkerApi($this->client($input));
            $worker = $action === 'get' ? $api->get(
                (string) $input->getArgument('instance-id'),
                (string) $input->getArgument('worker-id'),
            ) : $api->{$action}(
                (string) $input->getArgument('instance-id'),
                (string) $input->getArgument('worker-id'),
            );
            $this->renderer->render($output, $worker, $this->outputFormat($input));
            return 0;
        } catch (\Throwable $exception) {
            return $this->fail($output, $exception->getMessage(), $this->exceptionCode($exception));
        }
    }
}
