<?php

declare(strict_types=1);

namespace AmezmoCli\Support;

final class SecretRedactor
{
    public function __construct(private readonly ?string $secret = null) {}

    public function redact(string $value): string
    {
        if ($this->secret !== null && $this->secret !== '') {
            $value = str_replace($this->secret, '[REDACTED]', $value);
        }
        return preg_replace('/(Authorization\\s*:\\s*Bearer\\s+|AMEZMO_API_KEY[=:\s]+|--api-key[=\s]+)[^\\s,}]+/i', '$1[REDACTED]', $value) ?? $value;
    }

    public function mask(string $secret): string
    {
        return strlen($secret) <= 4 ? '****' : str_repeat('*', max(4, strlen($secret) - 4)) . substr($secret, -4);
    }
}
