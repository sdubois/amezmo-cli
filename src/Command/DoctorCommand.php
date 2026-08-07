<?php

declare(strict_types=1);

namespace AmezmoCli\Command;

use AmezmoCli\Config\ConfigPaths;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class DoctorCommand extends AbstractCommand
{
    public function __construct(\AmezmoCli\Auth\CredentialStoreInterface $credentials, \AmezmoCli\Config\ConfigLoader $config, \AmezmoCli\Output\ResultRenderer $renderer)
    {
        parent::__construct($credentials, $config, $renderer);
        $this->setName('doctor');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = ConfigPaths::file();
        $configExists = is_file($path);
        $config = $this->config->load($this->options($input));
        $checks = [
            'php' => PHP_VERSION,
            'php_supported' => version_compare(PHP_VERSION, '8.2.0', '>='),
            'config_path' => $path,
            'config_exists' => $configExists,
            'config_readable' => !$configExists || is_readable($path),
            'api_key_available' => $config->apiKey !== null,
            'api_key_source' => $config->apiKeySource,
        ];
        $healthy = $checks['php_supported'] && $checks['config_readable'];
        if ($this->outputFormat($input) === 'table') {
            foreach ($checks as $name => $value) {
                $output->writeln(sprintf('%-18s %s', $name . ':', is_bool($value) ? ($value ? 'yes' : 'no') : (string) $value));
            }
        } else {
            $this->renderer->render($output, $checks, $this->outputFormat($input));
        }
        return $healthy ? 0 : 1;
    }
}
