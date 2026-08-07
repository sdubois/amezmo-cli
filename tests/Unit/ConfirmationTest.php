<?php

declare(strict_types=1);

namespace AmezmoCli\Tests\Unit;

use AmezmoCli\Exception\ConfirmationRequiredException;
use AmezmoCli\Support\Confirmation;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

final class ConfirmationTest extends TestCase
{
    public function testNonInteractiveOperationsRequireYes(): void
    {
        $this->expectException(ConfirmationRequiredException::class);
        $input = new ArrayInput([]);
        $input->setInteractive(false);
        (new Confirmation())->require($input, new BufferedOutput(), 'Terminate instance?', false);
    }

    public function testYesBypassesConfirmation(): void
    {
        (new Confirmation())->require(new ArrayInput([]), new BufferedOutput(), 'Terminate instance?', true);
        self::assertTrue(true);
    }
}
