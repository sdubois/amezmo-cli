<?php

declare(strict_types=1);

namespace AmezmoCli\Tests\Unit;

use AmezmoCli\Config\ConfigPaths;
use PHPUnit\Framework\TestCase;

final class ConfigPathsTest extends TestCase
{
    public function testPlatformPaths(): void
    {
        $xdg = getenv('XDG_CONFIG_HOME');
        putenv('XDG_CONFIG_HOME');
        try {
            self::assertSame('/home/a/.config/amezmo-cli/config.json', ConfigPaths::file('/home/a', 'Linux'));
            self::assertSame('/custom/config/amezmo-cli/config.json', ConfigPaths::file('/home/a', 'Linux', '/custom/config'));
            self::assertSame('/home/a/Library/Application Support/amezmo-cli/config.json', ConfigPaths::file('/home/a', 'Darwin'));
            self::assertSame('C:\\Users\\a\\amezmo-cli\\config.json', ConfigPaths::file('C:\\Users\\a', 'Windows', null, 'C:\\Users\\a'));
        } finally {
            putenv($xdg === false ? 'XDG_CONFIG_HOME' : 'XDG_CONFIG_HOME=' . $xdg);
        }
    }
}
