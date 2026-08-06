<?php

declare(strict_types=1);

namespace AmezmoCli\Command\Auth;

use AmezmoCli\Command\AbstractCommand;
use AmezmoCli\Support\SecretRedactor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class StatusCommand extends AbstractCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $c = $this->config->load($this->options($input));
        if ($c->apiKey === null) {
            $output->writeln('Not authenticated.');
            return 3;
        }
        $data = ['available' => true, 'source' => $c->apiKeySource, 'key' => (new SecretRedactor())->mask($c->apiKey)];
        if (!$input->getOption('no-validate')) {
            try {
                (new \AmezmoCli\Api\AccountApi($this->client($input)))->currentUser();
                $data['valid'] = true;
            } catch (\Throwable) {
                $data['valid'] = false;
                return $this->fail($output, 'The configured API key could not be validated.', 3);
            }
        }
        $this->renderer->render($output, $data, $this->outputFormat($input));
        return 0;
    }
    protected function configure(): void
    {
        parent::configure();
        $this->addOption('no-validate', null, \Symfony\Component\Console\Input\InputOption::VALUE_NONE);
    }
}
