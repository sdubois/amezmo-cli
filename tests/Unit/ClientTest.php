<?php

declare(strict_types=1);

namespace AmezmoCli\Tests\Unit;

use AmezmoCli\Api\AmezmoClient;
use AmezmoCli\Api\RegionApi;
use AmezmoCli\Api\WorkerApi;
use AmezmoCli\Api\InstanceApi;
use AmezmoCli\Api\EnvironmentApi;
use AmezmoCli\Api\CronApi;
use AmezmoCli\Exception\ApiException;
use AmezmoCli\Exception\AuthenticationException;
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

    public function testDocumentedInstanceMutationsUseFormAndDeleteEndpoints(): void
    {
        $calls = [];
        $http = new MockHttpClient(static function (string $method, string $url, array $options) use (&$calls): MockResponse {
            $calls[] = [$method, $url, $options];
            return new MockResponse($method === 'POST' ? '{"id":42,"state":"Launching"}' : '{"id":42,"state":"Deleting"}', ['http_code' => $method === 'POST' ? 201 : 200]);
        });
        $api = new InstanceApi(new AmezmoClient('https://example.test/v1', 'test-key', 5, false, $http));
        self::assertSame(42, $api->create(['runtime' => 'php', 'instance_type' => 'business', 'region' => 'lb2-us'])['id']);
        self::assertSame('Deleting', $api->terminate('42')['state']);
        self::assertSame('POST', $calls[0][0]);
        self::assertSame('https://example.test/v1/instances', $calls[0][1]);
        self::assertSame('DELETE', $calls[1][0]);
        self::assertSame('https://example.test/v1/instances/42', $calls[1][1]);
        self::assertSame('runtime=php&instance_type=business&region=lb2-us', $calls[0][2]['body']);
    }

    public function testEnvironmentUpdateUsesDocumentedPatchEndpoint(): void
    {
        $http = new MockHttpClient(static function (string $method, string $url, array $options): MockResponse {
            self::assertSame('PATCH', $method);
            self::assertSame('https://example.test/v1/instances/42/environments/staging', $url);
            self::assertSame('ssh_enabled=0', $options['body']);
            return new MockResponse('{"id":5501,"ssh_enabled":false}', ['http_code' => 200]);
        });
        $environment = new EnvironmentApi(new AmezmoClient('https://example.test/v1', 'test-key', 5, false, $http));
        self::assertFalse($environment->update('42', 'staging', ['ssh_enabled' => false])['ssh_enabled']);
    }

    public function testWorkerLifecycleActionsUseDocumentedEndpoints(): void
    {
        $http = new MockHttpClient(static function (string $method, string $url): MockResponse {
            self::assertSame('POST', $method);
            return new MockResponse('{"status":"ok"}', ['http_code' => 200]);
        });
        $api = new WorkerApi(new AmezmoClient('https://example.test/v1', 'test-key', 5, false, $http));
        self::assertSame('ok', $api->start('42', '838')['status']);
        self::assertSame('ok', $api->stop('42', '838')['status']);
        self::assertSame('ok', $api->restart('42', '838')['status']);
    }

    public function testDeploymentCancelUsesDocumentedEndpoint(): void
    {
        $http = new MockHttpClient(static function (string $method, string $url): MockResponse {
            self::assertSame('POST', $method);
            self::assertSame('https://example.test/v1/instances/3503/deployments/838/cancel', $url);
            return new MockResponse('{"id":838,"status":"cancelling"}', ['http_code' => 200]);
        });
        $deployment = new \AmezmoCli\Api\DeploymentApi(new AmezmoClient('https://example.test/v1', 'test-key', 5, false, $http));
        self::assertSame('cancelling', $deployment->cancel('3503', '838')['status']);
    }

    public function testMultipartDeploymentRequestIncludesArchiveAndFields(): void
    {
        $archive = tempnam(sys_get_temp_dir(), 'amezmo-cli-');
        self::assertNotFalse($archive);
        file_put_contents($archive, 'archive-content');
        try {
            $http = new MockHttpClient(static function (string $method, string $url, array $options): MockResponse {
                self::assertSame('POST', $method);
                self::assertSame('https://example.test/v1/instances/3503/deployments', $url);
                self::assertStringContainsString('multipart/form-data', implode('; ', $options['headers']));
                self::assertIsCallable($options['body']);
                return new MockResponse('{"id":839,"status":"pending"}', ['http_code' => 201]);
            });
            $deployment = new \AmezmoCli\Api\DeploymentApi(new AmezmoClient('https://example.test/v1', 'test-key', 5, false, $http));
            self::assertSame('pending', $deployment->create('3503', 'staging', $archive, ['branch' => 'main'])['status']);
        } finally {
            unlink($archive);
        }
    }

    public function testCronReadsUseDocumentedEndpoints(): void
    {
        $urls = [];
        $http = new MockHttpClient(static function (string $method, string $url) use (&$urls): MockResponse {
            self::assertSame('GET', $method);
            $urls[] = $url;
            $entry = '{"id":412,"name":"Laravel-task-scheduler","expression":"* * * * *","environment_name":"production"}';
            return new MockResponse(str_ends_with($url, '/cron') ? '[' . $entry . ']' : $entry, ['http_code' => 200]);
        });
        $api = new CronApi(new AmezmoClient('https://example.test/v1', 'test-key', 5, false, $http));
        self::assertSame('production', $api->list('3503')[0]['environment_name']);
        self::assertSame(412, $api->get('3503', '412')['id']);
        self::assertSame(['https://example.test/v1/instances/3503/cron', 'https://example.test/v1/instances/3503/cron/412'], $urls);
    }

    public function testCronMutationsSendUrlEncodedFormFields(): void
    {
        $calls = [];
        $http = new MockHttpClient(static function (string $method, string $url, array $options) use (&$calls): MockResponse {
            $calls[] = [$method, $url, $options['body']];
            return new MockResponse('{"id":412,"expression":"*/5 * * * *","expression_alias":"@every_5_minutes"}', ['http_code' => $method === 'POST' ? 201 : 200]);
        });
        $api = new CronApi(new AmezmoClient('https://example.test/v1', 'test-key', 5, false, $http));
        $api->create('3503', ['name' => 'scheduler', 'command' => "#!/bin/bash\nphp artisan schedule:run\n", 'expression' => '* * * * *', 'environment' => 'production']);
        self::assertSame('@every_5_minutes', $api->update('3503', '412', ['expression' => '@every_5_minutes'])['expression_alias']);
        self::assertSame(['POST', 'https://example.test/v1/instances/3503/cron'], [$calls[0][0], $calls[0][1]]);
        self::assertSame('name=scheduler&command=%23%21%2Fbin%2Fbash%0Aphp+artisan+schedule%3Arun%0A&expression=%2A+%2A+%2A+%2A+%2A&environment=production', $calls[0][2]);
        self::assertSame(['PATCH', 'https://example.test/v1/instances/3503/cron/412', 'expression=%40every_5_minutes'], $calls[1]);
    }

    public function testErrorResponseIncludesValidationErrorsCodeAndRequestId(): void
    {
        $http = new MockHttpClient(new MockResponse('{"http_status":422,"type":"invalid_request_error","code":"validation_error","message":"The request you provided failed validation.","errors":{"expression":"This is an invalid cron expression."},"request_id":"req_01HZX9C4Q2","error":null}', ['http_code' => 422]));
        try {
            (new CronApi(new AmezmoClient('https://example.test/v1', 'test-key', 5, false, $http)))->update('3503', '412', ['expression' => 'every other tuesday']);
            self::fail('Expected an API exception.');
        } catch (ApiException $exception) {
            self::assertSame(422, $exception->statusCode);
            self::assertSame('invalid_request_error', $exception->errorType());
            self::assertSame('validation_error', $exception->errorCode());
            self::assertSame('req_01HZX9C4Q2', $exception->requestId());
            self::assertSame('The request you provided failed validation.' . PHP_EOL . '  expression: This is an invalid cron expression. (code: validation_error, request_id: req_01HZX9C4Q2)', $exception->getMessage());
        }
    }

    public function testNotFoundKeepsApiMessageAndFallsBackWhenMissing(): void
    {
        $http = new MockHttpClient([
            new MockResponse('{"type":"invalid_request_error","code":"unknown_resource_error","message":"Environment staging does not exist."}', ['http_code' => 404]),
            new MockResponse('', ['http_code' => 404]),
        ]);
        $client = new AmezmoClient('https://example.test/v1', 'test-key', 5, false, $http);
        foreach (['Environment staging does not exist. (code: unknown_resource_error)', 'Resource not found (HTTP 404). Check the resource type and identifier.'] as $expected) {
            try {
                $client->request('GET', '/instances/1/environments/staging');
                self::fail('Expected an API exception.');
            } catch (ApiException $exception) {
                self::assertSame($expected, $exception->getMessage());
            }
        }
    }

    public function testUnauthenticatedResponseRaisesAuthenticationException(): void
    {
        $http = new MockHttpClient(new MockResponse('{"type":"authentication_error","code":"invalid_credentials","message":"The API key was rejected."}', ['http_code' => 401]));
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('The API key was rejected. (code: invalid_credentials)');
        (new AmezmoClient('https://example.test/v1', 'bad-key', 5, false, $http))->request('GET', '/me/');
    }

    public function testMultipartErrorUsesApiMessage(): void
    {
        $archive = tempnam(sys_get_temp_dir(), 'amezmo-cli-');
        self::assertNotFalse($archive);
        file_put_contents($archive, 'archive-content');
        try {
            $http = new MockHttpClient(new MockResponse('{"type":"invalid_request_error","code":"invalid_instance_state","message":"The environment is paused."}', ['http_code' => 409]));
            $this->expectException(ApiException::class);
            $this->expectExceptionMessage('The environment is paused. (code: invalid_instance_state)');
            (new \AmezmoCli\Api\DeploymentApi(new AmezmoClient('https://example.test/v1', 'test-key', 5, false, $http)))->create('3503', 'staging', $archive);
        } finally {
            unlink($archive);
        }
    }
}
