<?php

declare(strict_types=1);

namespace AmezmoCli\Command;

use AmezmoCli\Api\RegionApi;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class RegionsCommand extends AbstractCommand
{
    public function __construct(\AmezmoCli\Auth\CredentialStoreInterface $credentials, \AmezmoCli\Config\ConfigLoader $config, \AmezmoCli\Output\ResultRenderer $renderer)
    {
        parent::__construct($credentials, $config, $renderer);
        $this->setName('regions');
    }

    protected function configure(): void
    {
        parent::configure();
        $this->addArgument('action', InputArgument::REQUIRED, 'list');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getArgument('action') !== 'list') {
            return $this->fail($output, 'Unknown regions action. Use: regions list', 2);
        }

        try {
            $regions = (new RegionApi($this->client($input)))->list();
            $this->renderer->render($output, $regions, $this->outputFormat($input), [
                'ID' => 'id',
                'Country' => 'iso_country_code',
                'Name' => 'name',
            ]);
            return 0;
        } catch (\Throwable $exception) {
            return $this->fail($output, $exception->getMessage(), 5);
        }
    }
}
