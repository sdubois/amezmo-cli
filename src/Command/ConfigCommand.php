<?php

declare(strict_types=1);

namespace AmezmoCli\Command;

use AmezmoCli\Config\ConfigPaths;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ConfigCommand extends AbstractCommand
{
    public function __construct(\AmezmoCli\Auth\CredentialStoreInterface $credentials, \AmezmoCli\Config\ConfigLoader $config, \AmezmoCli\Output\ResultRenderer $renderer)
    {
        parent::__construct($credentials, $config, $renderer);
        $this->setName('config');
    }

    protected function configure(): void
    {
        parent::configure();
        $this->addArgument('action', InputArgument::REQUIRED, 'path');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getArgument('action') !== 'path') {
            return $this->fail($output, 'Usage: config path', 2);
        }
        $output->writeln(ConfigPaths::file());
        return 0;
    }
}
