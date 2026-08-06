<?php

declare(strict_types=1);

namespace AmezmoCli\Exception;

use RuntimeException;

class ApiException extends RuntimeException
{
    /** @param array<string, mixed> $response */
    public function __construct(string $message, public readonly int $statusCode = 0, public readonly array $response = [])
    {
        parent::__construct($message);
    }
}
