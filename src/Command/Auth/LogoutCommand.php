<?php

declare(strict_types=1);

namespace AmezmoCli\Command\Auth;

use AmezmoCli\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class LogoutCommand extends AbstractCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->credentials->removeApiKey();
        $output->writeln('Stored API key removed.');
        if (getenv('AMEZMO_API_KEY') !== false) {
            $output->writeln('<comment>AMEZMO_API_KEY is still set and remains active.</comment>');
        } return 0;
    }
}
