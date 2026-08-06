<?php

declare(strict_types=1);

namespace AmezmoCli\Api;

final class RegionApi
{
    public function __construct(private readonly AmezmoClient $client) {}

    /** @return list<array<string, mixed>> */
    public function list(): array
    {
        $regions = $this->client->request('GET', '/regions');
        return array_values($regions);
    }

    /** @return array<string, mixed> */
    public function get(string $id): array
    {
        return $this->client->request('GET', '/regions/' . rawurlencode($id));
    }
}
