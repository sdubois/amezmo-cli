<?php

declare(strict_types=1);

namespace AmezmoCli\Tests\Unit;

use AmezmoCli\Auth\CredentialStoreInterface;
use AmezmoCli\Command\CronCommand;
use AmezmoCli\Config\ConfigLoader;
use AmezmoCli\Output\ResultRenderer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class CronCommandTest extends TestCase
{
    /**
     * Each case fails validation before any request is sent.
     *
     * @param array<string, mixed> $input
     */
    #[DataProvider('invalidInputs')]
    public function testInvalidInputIsRejectedAsUsageError(array $input, string $message): void
    {
        $tester = new CommandTester($this->command());
        self::assertSame(2, $tester->execute($input + ['--api-url' => 'https://invalid.test/v1', '--api-key' => 'test-key', '--yes' => true]));
        self::assertStringContainsString($message, $tester->getDisplay());
    }

    /** @return iterable<string, array{array<string, mixed>, string}> */
    public static function invalidInputs(): iterable
    {
        yield 'unknown action' => [['action' => 'delete', 'instance-id' => '3503', 'cron-id' => '412'], 'Usage: cron list'];
        yield 'get without id' => [['action' => 'get', 'instance-id' => '3503'], 'Usage: cron list'];
        yield 'create missing script' => [['action' => 'create', 'instance-id' => '3503', '--name' => 'scheduler', '--environment' => 'production', '--expression' => '@daily'], 'Usage: cron list'];
        yield 'create bad name' => [['action' => 'create', 'instance-id' => '3503', '--name' => '-scheduler', '--environment' => 'production', '--expression' => '@daily', '--command' => 'true'], 'Cron name must start'];
        yield 'create long name' => [['action' => 'create', 'instance-id' => '3503', '--name' => str_repeat('a', 64), '--environment' => 'production', '--expression' => '@daily', '--command' => 'true'], 'Cron name must start'];
        yield 'create bad environment' => [['action' => 'create', 'instance-id' => '3503', '--name' => 'scheduler', '--environment' => 'dev', '--expression' => '@daily', '--command' => 'true'], 'production or staging'];
        yield 'update without fields' => [['action' => 'update', 'instance-id' => '3503', 'cron-id' => '412'], 'Provide --command'];
        yield 'command and file' => [['action' => 'update', 'instance-id' => '3503', 'cron-id' => '412', '--command' => 'true', '--command-file' => __FILE__], 'not both'];
        yield 'missing file' => [['action' => 'update', 'instance-id' => '3503', 'cron-id' => '412', '--command-file' => '/nonexistent/script.sh'], 'does not exist'];
    }

    private function command(): CronCommand
    {
        $store = new class implements CredentialStoreInterface {
            public function getApiKey(): ?string
            {
                return null;
            }

            public function storeApiKey(string $apiKey): void {}

            public function removeApiKey(): void {}
        };
        return new CronCommand($store, new ConfigLoader($store), new ResultRenderer());
    }
}
