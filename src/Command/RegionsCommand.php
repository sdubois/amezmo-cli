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
        $this->addArgument('action', InputArgument::REQUIRED, 'list or get')->addArgument('region-id', InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $api = new RegionApi($this->client($input));
            if ($input->getArgument('action') === 'list') {
                $result = $api->list();
                $columns = ['ID' => 'id', 'Country' => 'iso_country_code', 'Name' => 'name'];
            } elseif ($input->getArgument('action') === 'get' && is_string($input->getArgument('region-id')) && $input->getArgument('region-id') !== '') {
                $result = $api->get((string) $input->getArgument('region-id'));
                $columns = [];
            } else {
                return $this->fail($output, 'Usage: regions list or regions get <region-id>', 2);
            }
            $this->renderer->render($output, $result, $this->outputFormat($input), $columns);
            return 0;
        } catch (\Throwable $exception) {
            return $this->fail($output, $exception->getMessage(), $this->exceptionCode($exception));
        }
    }
}
