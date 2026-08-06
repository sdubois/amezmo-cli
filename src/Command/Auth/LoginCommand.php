<?php

declare(strict_types=1);

namespace AmezmoCli\Command\Auth;

use AmezmoCli\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Output\OutputInterface;

final class LoginCommand extends AbstractCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $key = $input->getOption('api-key');
        if (!is_string($key) || $key === '') {
            if ($input->getOption('no-interaction')) {
                return $this->fail($output, 'An API key is required in non-interactive mode.', 2);
            }
            $question = new Question('Amezmo API key: ');
            $question->setHidden(true);
            $helper = $this->getHelper('question');
            if (!$helper instanceof QuestionHelper) {
                return $this->fail($output, 'Interactive questions are unavailable.', 1);
            } $key = $helper->ask($input, $output, $question);
        }
        if (!is_string($key) || trim($key) === '') {
            return $this->fail($output, 'An API key is required.', 2);
        }
        try {
            $options = $this->options($input);
            $options['api-key'] = trim($key);
            $user = (new \AmezmoCli\Api\AccountApi(new \AmezmoCli\Api\AmezmoClient($this->config->load($options)->apiUrl, trim($key), $this->config->load($options)->timeout)))->currentUser();
            $this->credentials->storeApiKey(trim($key));
            $output->writeln('Authenticated as ' . (string) ($user['email'] ?? 'the current account') . '.');
            return 0;
        } catch (\Throwable $e) {
            return $this->fail($output, $e->getMessage(), 3);
        }
    }
    protected function configure(): void
    {
        parent::configure();
        $this->addOption('no-interaction', null, InputOption::VALUE_NONE);
    }
}
