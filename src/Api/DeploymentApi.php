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

    /** @return array<string, mixed> */
    public function cancel(string $instanceId, string $deploymentId): array
    {
        return $this->client->request('POST', '/instances/' . rawurlencode($instanceId) . '/deployments/' . rawurlencode($deploymentId) . '/cancel');
    }

    /**
     * @param array<string, string> $fields
     * @return array<string, mixed>
     */
    public function create(string $instanceId, string $environment, string $archive, array $fields = []): array
    {
        return $this->client->multipart('/instances/' . rawurlencode($instanceId) . '/deployments', ['environment' => $environment] + $fields, $archive);
    }
}
