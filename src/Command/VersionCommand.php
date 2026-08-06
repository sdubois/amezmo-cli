<?php

declare(strict_types=1);

namespace AmezmoCli\Command;

use AmezmoCli\Version;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class VersionCommand extends AbstractCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('amezmo-cli ' . Version::VALUE);
        return 0;
    }
}
