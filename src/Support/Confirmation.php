<?php

declare(strict_types=1);

namespace AmezmoCli\Support;

use AmezmoCli\Exception\ConfirmationRequiredException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

final class Confirmation
{
    public function __construct(private readonly QuestionHelper $questions = new QuestionHelper()) {}

    public function require(InputInterface $input, OutputInterface $output, string $message, bool $yes): void
    {
        if ($yes) {
            return;
        }
        if (!$input->isInteractive()) {
            throw new ConfirmationRequiredException('Confirmation is required. Re-run interactively or pass --yes.');
        }
        $question = new ConfirmationQuestion($message . ' [y/N] ', false);
        if (!$this->questions->ask($input, $output, $question)) {
            throw new ConfirmationRequiredException('Operation cancelled.');
        }
    }
}
