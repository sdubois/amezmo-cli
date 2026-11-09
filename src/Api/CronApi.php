<?php

declare(strict_types=1);

namespace AmezmoCli\Api;

final class CronApi
{
    public function __construct(private readonly AmezmoClient $client) {}

    /** @return list<array<string, mixed>> */
    public function list(string $instanceId): array
    {
        return array_values($this->client->request('GET', '/instances/' . rawurlencode($instanceId) . '/cron'));
    }

    /** @return array<string, mixed> */
    public function get(string $instanceId, string $cronId): array
    {
        return $this->client->request('GET', '/instances/' . rawurlencode($instanceId) . '/cron/' . rawurlencode($cronId));
    }

    /**
     * @param array{name: string, command: string, expression: string, environment: string} $fields
     * @return array<string, mixed>
     */
    public function create(string $instanceId, array $fields): array
    {
        return $this->client->formRequest('POST', '/instances/' . rawurlencode($instanceId) . '/cron', $fields);
    }

    /**
     * @param array{command?: string, expression?: string} $fields
     * @return array<string, mixed>
     */
    public function update(string $instanceId, string $cronId, array $fields): array
    {
        return $this->client->formRequest('PATCH', '/instances/' . rawurlencode($instanceId) . '/cron/' . rawurlencode($cronId), $fields);
    }
}
