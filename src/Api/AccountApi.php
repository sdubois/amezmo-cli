<?php

declare(strict_types=1);

namespace AmezmoCli\Api;

final class AccountApi
{
    public function __construct(private readonly AmezmoClient $client) {}
    /** @return array<string, mixed> */
    public function currentUser(): array
    {
        return $this->client->request('GET', '/me/');
    }
}
