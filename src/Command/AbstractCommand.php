<?php

declare(strict_types=1);

namespace AmezmoCli\Command;

use AmezmoCli\Api\AmezmoClient;
use AmezmoCli\Auth\CredentialStoreInterface;
use AmezmoCli\Config\ConfigLoader;
use AmezmoCli\Output\ResultRenderer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{
    public function __construct(protected readonly CredentialStoreInterface $credentials, protected readonly ConfigLoader $config, protected readonly ResultRenderer $renderer)
    {
        parent::__construct();
    }
    protected function configure(): void
    {
        $this->addOption('api-url', null, \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED)
            ->addOption('api-key', null, \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED)
            ->addOption('output', 'o', \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'table, json, or yaml', 'table')
            ->addOption('no-color', null, \Symfony\Component\Console\Input\InputOption::VALUE_NONE)
            ->addOption('timeout', null, \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED)
            ->addOption('debug', null, \Symfony\Component\Console\Input\InputOption::VALUE_NONE);
    }
    protected function client(InputInterface $input): AmezmoClient
    {
        $c = $this->config->load($input->getOptions());
        return new AmezmoClient($c->apiUrl, $c->apiKey, $c->timeout, $c->debug);
    }
    /** @return array<string, mixed> */
    protected function options(InputInterface $input): array
    {
        return $input->getOptions();
    }
    protected function outputFormat(InputInterface $input): string
    {
        return $this->config->load($this->options($input))->output;
    }
    protected function fail(OutputInterface $output, string $message, int $code = 1): int
    {
        $stream = $output instanceof \Symfony\Component\Console\Output\ConsoleOutputInterface ? $output->getErrorOutput() : $output;
        $stream->writeln('<error>' . $message . '</error>');
        return $code;
    }
}
