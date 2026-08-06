<?php

declare(strict_types=1);

namespace AmezmoCli\Api;

final class WorkerApi
{
    public function __construct(private readonly AmezmoClient $client) {}

    /** @return array<string, mixed> */
    public function get(string $instanceId, string $workerId): array
    {
        return $this->client->request('GET', '/instances/' . rawurlencode($instanceId) . '/workers/' . rawurlencode($workerId));
    }
}
