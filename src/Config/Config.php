<?php

declare(strict_types=1);

namespace AmezmoCli\Config;

final readonly class Config
{
    public function __construct(public string $apiUrl, public ?string $apiKey, public string $apiKeySource, public string $output, public int $timeout, public bool $debug) {}
}
