<?php

declare(strict_types=1);

namespace AmezmoCli\Api;

final class DeploymentApi
{
    public function __construct(private readonly AmezmoClient $client) {}

    /** @return array<string, mixed> */
    public function get(string $instanceId, string $deploymentId): array
    {
        return $this->client->request('GET', '/instances/' . rawurlencode($instanceId) . '/deployments/' . rawurlencode($deploymentId));
    }
}
