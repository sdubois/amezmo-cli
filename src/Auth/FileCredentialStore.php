<?php

declare(strict_types=1);

namespace AmezmoCli\Auth;

use AmezmoCli\Config\ConfigPaths;
use AmezmoCli\Exception\ConfigurationException;

final class FileCredentialStore implements CredentialStoreInterface
{
    public function __construct(private readonly string $path = '') {}

    private function file(): string
    {
        return $this->path !== '' ? $this->path : ConfigPaths::file();
    }

    public function getApiKey(): ?string
    {
        if (!is_file($this->file())) {
            return null;
        }
        $data = json_decode((string) file_get_contents($this->file()), true);
        return is_array($data) && isset($data['api_key']) && is_string($data['api_key']) ? $data['api_key'] : null;
    }

    public function storeApiKey(string $apiKey): void
    {
        $file = $this->file();
        $directory = dirname($file);
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new ConfigurationException('Unable to create configuration directory.');
        }
        if (file_put_contents($file, json_encode(['api_key' => $apiKey], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . PHP_EOL, LOCK_EX) === false) {
            throw new ConfigurationException('Unable to write configuration file.');
        }
        if (PHP_OS_FAMILY !== 'Windows') {
            @chmod($file, 0600);
        }
    }

    public function removeApiKey(): void
    {
        if (is_file($this->file())) {
            @unlink($this->file());
        }
    }
}
