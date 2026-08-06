<?php

declare(strict_types=1);

namespace AmezmoCli\Tests\Unit;

use AmezmoCli\Api\AmezmoClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class ClientTest extends TestCase
{
    public function testCurrentUserRequestUsesBearerAndNormalizesUrl(): void
    {
        $http = new MockHttpClient(static function (string $method, string $url, array $options): MockResponse {
            self::assertSame('GET', $method);
            self::assertSame('https://example.test/v1/me/', $url);
            self::assertSame(['Authorization: Bearer test-key'], $options['normalized_headers']['authorization']);
            return new MockResponse('{"email":"example@example.com"}', ['http_code' => 200]);
        });
        self::assertSame(['email' => 'example@example.com'], (new AmezmoClient('https://example.test/v1/', 'test-key', 5, false, $http))->request('GET', '/me/'));
    }
}
