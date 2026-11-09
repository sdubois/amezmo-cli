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

    /** The coarse error class, such as invalid_request_error or rate_limit_error. */
    public function errorType(): ?string
    {
        return isset($this->response['type']) && is_string($this->response['type']) ? $this->response['type'] : null;
    }

    /** The specific, machine-readable reason, such as validation_error or cron_entry_exists. */
    public function errorCode(): ?string
    {
        return isset($this->response['code']) && is_string($this->response['code']) ? $this->response['code'] : null;
    }

    public function requestId(): ?string
    {
        return isset($this->response['request_id']) && is_string($this->response['request_id']) ? $this->response['request_id'] : null;
    }
}
