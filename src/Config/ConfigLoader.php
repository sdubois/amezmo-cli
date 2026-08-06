<?php

declare(strict_types=1);

namespace AmezmoCli\Config;

use AmezmoCli\Auth\CredentialStoreInterface;

final class ConfigLoader
{
    public function __construct(private readonly CredentialStoreInterface $credentials) {}

    /** @param array<string, mixed> $options */
    public function load(array $options = []): Config
    {
        $env = static fn(string $name): ?string => (($value = getenv($name)) !== false && $value !== '') ? $value : null;
        $key = $options['api-key'] ?? $env('AMEZMO_API_KEY') ?? $this->credentials->getApiKey();
        $source = isset($options['api-key']) ? 'command line' : ($env('AMEZMO_API_KEY') !== null ? 'environment variable' : ($key !== null ? 'config file' : 'none'));
        return new Config($options['api-url'] ?? $env('AMEZMO_API_URL') ?? 'https://api.amezmo.com/v1', $key, $source, strtolower((string) ($options['output'] ?? $env('AMEZMO_OUTPUT') ?? 'table')), (int) ($options['timeout'] ?? $env('AMEZMO_TIMEOUT') ?? 30), (bool) ($options['debug'] ?? false));
    }
}
