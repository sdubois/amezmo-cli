<?php

declare(strict_types=1);

namespace AmezmoCli;

use AmezmoCli\Auth\FileCredentialStore;
use AmezmoCli\Command\Auth\LoginCommand;
use AmezmoCli\Command\Auth\LogoutCommand;
use AmezmoCli\Command\Auth\StatusCommand;
use AmezmoCli\Command\AuthCommand;
use AmezmoCli\Command\VersionCommand;
use AmezmoCli\Command\WhoamiCommand;
use AmezmoCli\Config\ConfigLoader;
use AmezmoCli\Output\ResultRenderer;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\OutputInterface;

final class Application extends ConsoleApplication
{
    public function __construct()
    {
        parent::__construct('amezmo-cli', Version::VALUE);
        $store = new FileCredentialStore();
        $loader = new ConfigLoader($store);
        $renderer = new ResultRenderer();
        $commands = [
            ['version', new VersionCommand($store, $loader, $renderer)],
            ['auth', new AuthCommand($store, $loader, $renderer)],
            ['whoami', new WhoamiCommand($store, $loader, $renderer)],
        ];
        foreach ($commands as [$name, $command]) {
            $this->add($command->setName($name));
        }
    }

    public function run(?\Symfony\Component\Console\Input\InputInterface $input = null, ?\Symfony\Component\Console\Output\OutputInterface $output = null): int
    {
        $input ??= new ArgvInput();
        if (getenv('NO_COLOR') !== false || $input->hasParameterOption('--no-color')) {
            $output ??= new \Symfony\Component\Console\Output\ConsoleOutput();
        }
        try {
            return parent::run($input, $output);
        } catch (\Throwable $e) {
            $stream = $output instanceof \Symfony\Component\Console\Output\ConsoleOutputInterface ? $output->getErrorOutput() : ($output ?? new \Symfony\Component\Console\Output\ConsoleOutput());
            $stream->writeln('<error>' . $e->getMessage() . '</error>');
            return 1;
        }
    }
}
