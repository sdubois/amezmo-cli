<?php

declare(strict_types=1);

namespace AmezmoCli\Tests\Unit;

use AmezmoCli\Api\AmezmoClient;
use AmezmoCli\Api\RegionApi;
use AmezmoCli\Api\WorkerApi;
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

    public function testRegionListUsesDocumentedEndpoint(): void
    {
        $http = new MockHttpClient(static function (string $method, string $url): MockResponse {
            self::assertSame('GET', $method);
            self::assertSame('https://example.test/v1/regions', $url);
            return new MockResponse('[{"id":"lb2-us","iso_country_code":"US","name":"United States"}]', ['http_code' => 200]);
        });
        $regions = (new RegionApi(new AmezmoClient('https://example.test/v1', 'test-key', 5, false, $http)))->list();
        self::assertSame('lb2-us', $regions[0]['id']);
        self::assertSame('United States', $regions[0]['name']);
    }

    public function testWorkerGetUsesDocumentedEndpoint(): void
    {
        $http = new MockHttpClient(static function (string $method, string $url): MockResponse {
            self::assertSame('GET', $method);
            self::assertSame('https://example.test/v1/instances/3503/workers/838', $url);
            return new MockResponse('{"id":838,"name":"queue","status":"Running"}', ['http_code' => 200]);
        });
        $worker = (new WorkerApi(new AmezmoClient('https://example.test/v1', 'test-key', 5, false, $http)))->get('3503', '838');
        self::assertSame('queue', $worker['name']);
        self::assertSame('Running', $worker['status']);
    }
}
