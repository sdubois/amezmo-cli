<?php

declare(strict_types=1);

namespace AmezmoCli\Api;

use AmezmoCli\Exception\ApiException;
use AmezmoCli\Exception\AuthenticationException;
use AmezmoCli\Support\SecretRedactor;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class AmezmoClient
{
    public function __construct(private readonly string $baseUrl, private readonly ?string $apiKey, private readonly int $timeout = 30, private readonly bool $debug = false, private readonly ?HttpClientInterface $http = null) {}

    /**
     * @param array<string, scalar> $query
     * @param array<string, mixed>|null $json
     * @return array<string, mixed>
     */
    public function request(string $method, string $path, array $query = [], ?array $json = null): array
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($path, '/');
        $options = ['timeout' => $this->timeout, 'headers' => ['Accept' => 'application/json', 'User-Agent' => 'amezmo-cli/' . \AmezmoCli\Version::VALUE]];
        if ($this->apiKey !== null) {
            $options['headers']['Authorization'] = 'Bearer ' . $this->apiKey;
        }
        if ($query !== []) {
            $options['query'] = $query;
        }
        if ($json !== null) {
            $options['json'] = $json;
        }
        $response = ($this->http ?? HttpClient::create())->request($method, $url, $options);
        try {
            $status = $response->getStatusCode();
            $body = $response->getContent(false);
        } catch (\Throwable $e) {
            throw new ApiException('Amezmo API request failed: ' . $e->getMessage(), 0);
        }
        if ($this->debug) {
            fwrite(STDERR, (new SecretRedactor($this->apiKey))->redact(sprintf('%s %s -> %d', $method, $url, $status)) . PHP_EOL);
        }
        $data = $body === '' ? [] : json_decode($body, true);
        if ($status >= 400) {
            $message = is_array($data) && isset($data['message']) && is_string($data['message']) ? $data['message'] : 'Amezmo API returned HTTP ' . $status . '.';
            if ($status === 404) {
                $message = 'Resource not found (HTTP 404). Check the resource type and identifier.';
            }
            if ($status === 401) {
                throw new AuthenticationException($message, $status, is_array($data) ? $data : []);
            }
            throw new ApiException($message, $status, is_array($data) ? $data : []);
        }
        if ($body !== '' && !is_array($data)) {
            throw new ApiException('Amezmo API returned invalid JSON.', $status);
        }
        return is_array($data) ? $data : [];
    }

    /**
     * @param array<string, mixed> $fields
     * @return array<string, mixed>
     */
    public function formRequest(string $method, string $path, array $fields = []): array
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($path, '/');
        $headers = ['Accept' => 'application/json', 'User-Agent' => 'amezmo-cli/' . \AmezmoCli\Version::VALUE];
        if ($this->apiKey !== null) {
            $headers['Authorization'] = 'Bearer ' . $this->apiKey;
        }
        $options = ['timeout' => $this->timeout, 'headers' => $headers];
        if ($fields !== []) {
            $options['body'] = $fields;
        }
        $response = ($this->http ?? HttpClient::create())->request($method, $url, $options);
        try {
            $status = $response->getStatusCode();
            $body = $response->getContent(false);
        } catch (\Throwable $e) {
            throw new ApiException('Amezmo API request failed: ' . $e->getMessage(), 0);
        }
        $data = $body === '' ? [] : json_decode($body, true);
        if ($status >= 400) {
            $message = is_array($data) && isset($data['message']) && is_string($data['message']) ? $data['message'] : 'Amezmo API returned HTTP ' . $status . '.';
            if ($status === 404) {
                $message = 'Resource not found (HTTP 404). Check the resource type and identifier.';
            }
            if ($status === 401) {
                throw new AuthenticationException($message, $status, is_array($data) ? $data : []);
            }
            throw new ApiException($message, $status, is_array($data) ? $data : []);
        }
        if ($body !== '' && !is_array($data)) {
            throw new ApiException('Amezmo API returned invalid JSON.', $status);
        }
        return is_array($data) ? $data : [];
    }

    /**
     * @param array<string, string> $fields
     * @return array<string, mixed>
     */
    public function multipart(string $path, array $fields, string $archivePath): array
    {
        $parts = $fields;
        $parts['archive'] = DataPart::fromPath($archivePath);
        $form = new FormDataPart($parts);
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($path, '/');
        $options = [
            'timeout' => $this->timeout,
            'headers' => array_merge(['Accept' => 'application/json', 'User-Agent' => 'amezmo-cli/' . \AmezmoCli\Version::VALUE], $form->getPreparedHeaders()->toArray()),
            'body' => $form->bodyToIterable(),
        ];
        if ($this->apiKey !== null) {
            $options['headers']['Authorization'] = 'Bearer ' . $this->apiKey;
        }
        $response = ($this->http ?? HttpClient::create())->request('POST', $url, $options);
        try {
            $status = $response->getStatusCode();
            $body = $response->getContent(false);
        } catch (\Throwable $exception) {
            throw new ApiException('Amezmo API request failed: ' . $exception->getMessage(), 0);
        }
        $data = $body === '' ? [] : json_decode($body, true);
        if ($status >= 400) {
            throw new ApiException('Amezmo API returned HTTP ' . $status . '.', $status, is_array($data) ? $data : []);
        }
        if ($body !== '' && !is_array($data)) {
            throw new ApiException('Amezmo API returned invalid JSON.', $status);
        }
        return is_array($data) ? $data : [];
    }
}
