<?php

declare(strict_types=1);

namespace AmezmoCli\Api;

final class EnvironmentApi
{
    public function __construct(private readonly AmezmoClient $client) {}

    /** @return list<array<string, mixed>> */
    public function list(string $instanceId): array
    {
        return array_values($this->client->request('GET', '/instances/' . rawurlencode($instanceId) . '/environments'));
    }

    /** @return array<string, mixed> */
    public function get(string $instanceId, string $name): array
    {
        return $this->client->request('GET', '/instances/' . rawurlencode($instanceId) . '/environments/' . rawurlencode($name));
    }

    /**
     * @param array<string, mixed> $fields
     * @return array<string, mixed>
     */
    public function update(string $instanceId, string $name, array $fields): array
    {
        return $this->client->formRequest('PATCH', '/instances/' . rawurlencode($instanceId) . '/environments/' . rawurlencode($name), $fields);
    }
}
