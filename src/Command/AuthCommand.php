<?php

declare(strict_types=1);

namespace AmezmoCli\Command;

use AmezmoCli\Command\Auth\LoginCommand;
use AmezmoCli\Command\Auth\LogoutCommand;
use AmezmoCli\Command\Auth\StatusCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class AuthCommand extends AbstractCommand
{
    public function __construct(\AmezmoCli\Auth\CredentialStoreInterface $credentials, \AmezmoCli\Config\ConfigLoader $config, \AmezmoCli\Output\ResultRenderer $renderer)
    {
        parent::__construct($credentials, $config, $renderer);
        $this->setName('auth');
    }
    protected function configure(): void
    {
        parent::configure();
        $this->addArgument('action', InputArgument::REQUIRED, 'login, logout, or status')->addOption('no-validate', null, InputOption::VALUE_NONE);
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $commands = ['login' => LoginCommand::class, 'logout' => LogoutCommand::class, 'status' => StatusCommand::class];
        $action = (string) $input->getArgument('action');
        if (!isset($commands[$action])) {
            return $this->fail($output, 'Unknown auth action. Use login, logout, or status.', 2);
        }
        $arguments = [];
        foreach ($input->getOptions() as $name => $value) {
            if ($value !== null && $value !== false) {
                $arguments['--' . $name] = $value === true ? null : $value;
            }
        }
        $command = new $commands[$action]($this->credentials, $this->config, $this->renderer);
        $command->setHelperSet($this->getHelperSet());
        $subInput = new ArrayInput($arguments);
        $subInput->setInteractive($input->isInteractive());
        return $command->run($subInput, $output);
    }
}
