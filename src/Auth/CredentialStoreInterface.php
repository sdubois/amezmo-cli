<?php

declare(strict_types=1);

namespace AmezmoCli\Auth;

interface CredentialStoreInterface
{
    public function getApiKey(): ?string;
    public function storeApiKey(string $apiKey): void;
    public function removeApiKey(): void;
}
