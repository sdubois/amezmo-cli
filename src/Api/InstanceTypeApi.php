<?php

declare(strict_types=1);

namespace AmezmoCli\Api;

final class InstanceTypeApi
{
    public function __construct(private readonly AmezmoClient $client) {}

    /** @return list<array<string, mixed>> */
    public function list(): array
    {
        return array_values($this->client->request('GET', '/instance-types'));
    }
}
