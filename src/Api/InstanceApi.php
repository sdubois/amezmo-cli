<?php

declare(strict_types=1);

namespace AmezmoCli\Api;

final class InstanceApi
{
    public function __construct(private readonly AmezmoClient $client) {}

    /** @return list<array<string, mixed>> */
    public function list(?string $instanceType = null): array
    {
        $instances = $this->client->request('GET', '/instances', $instanceType === null ? [] : ['instance_type' => $instanceType]);
        return array_values($instances);
    }

    /** @return array<string, mixed> */
    public function get(string $id): array
    {
        return $this->client->request('GET', '/instances/' . rawurlencode($id));
    }
}
