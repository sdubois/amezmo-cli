<?php

declare(strict_types=1);

namespace AmezmoCli\Tests\Unit;

use AmezmoCli\Support\SecretRedactor;
use PHPUnit\Framework\TestCase;

final class SecretRedactorTest extends TestCase
{
    public function testSecretsAreRedacted(): void
    {
        $redactor = new SecretRedactor('secret-value');
        $result = $redactor->redact('Authorization: Bearer secret-value AMEZMO_API_KEY=other-value');
        self::assertStringNotContainsString('secret-value', $result);
        self::assertStringNotContainsString('other-value', $result);
    }
}
