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

    /** @return array<string, mixed> */
    public function start(string $instanceId, string $workerId): array
    {
        return $this->command($instanceId, $workerId, 'start');
    }

    /** @return array<string, mixed> */
    public function stop(string $instanceId, string $workerId): array
    {
        return $this->command($instanceId, $workerId, 'stop');
    }

    /** @return array<string, mixed> */
    public function restart(string $instanceId, string $workerId): array
    {
        return $this->command($instanceId, $workerId, 'restart');
    }

    /** @return array<string, mixed> */
    private function command(string $instanceId, string $workerId, string $action): array
    {
        return $this->client->request('POST', '/instances/' . rawurlencode($instanceId) . '/workers/' . rawurlencode($workerId) . '/' . $action);
    }
}
