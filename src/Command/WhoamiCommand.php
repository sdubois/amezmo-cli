<?php

declare(strict_types=1);

namespace AmezmoCli\Command;

use AmezmoCli\Exception\AuthenticationException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class WhoamiCommand extends AbstractCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->renderer->render($output, (new \AmezmoCli\Api\AccountApi($this->client($input)))->currentUser(), $this->outputFormat($input));
            return 0;
        } catch (AuthenticationException $e) {
            return $this->fail($output, $e->getMessage(), 3);
        } catch (\Throwable $e) {
            return $this->fail($output, $e->getMessage(), 5);
        }
    }
}
