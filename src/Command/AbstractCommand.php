<?php

declare(strict_types=1);

namespace AmezmoCli\Command;

use AmezmoCli\Api\AmezmoClient;
use AmezmoCli\Auth\CredentialStoreInterface;
use AmezmoCli\Config\ConfigLoader;
use AmezmoCli\Exception\ApiException;
use AmezmoCli\Exception\AuthenticationException;
use AmezmoCli\Exception\ConfirmationRequiredException;
use AmezmoCli\Output\ResultRenderer;
use AmezmoCli\Support\ExitCode;
use AmezmoCli\Support\Confirmation;
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

    protected function exceptionCode(\Throwable $exception): int
    {
        if ($exception instanceof ConfirmationRequiredException) {
            return ExitCode::ERROR;
        }
        if ($exception instanceof AuthenticationException) {
            return ExitCode::AUTH;
        }
        if ($exception instanceof ApiException) {
            if ($exception->errorType() === 'authentication_error') {
                return ExitCode::AUTH;
            }
            return match ($exception->statusCode) {
                401, 403 => ExitCode::AUTH,
                404 => ExitCode::NOT_FOUND,
                422 => ExitCode::VALIDATION,
                default => ExitCode::API,
            };
        }
        return ExitCode::API;
    }

    protected function addDestructiveOption(): void
    {
        $this->addOption('yes', null, \Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Skip the confirmation prompt.');
    }

    protected function confirmDestructive(InputInterface $input, OutputInterface $output, string $message): void
    {
        (new Confirmation())->require($input, $output, $message, (bool) $input->getOption('yes'));
    }
    protected function fail(OutputInterface $output, string $message, int $code = 1): int
    {
        $stream = $output instanceof \Symfony\Component\Console\Output\ConsoleOutputInterface ? $output->getErrorOutput() : $output;
        $stream->writeln('<error>' . $message . '</error>');
        return $code;
    }
}
