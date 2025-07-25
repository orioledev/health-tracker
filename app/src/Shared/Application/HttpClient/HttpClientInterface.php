<?php

declare(strict_types=1);

namespace App\Shared\Application\HttpClient;

interface HttpClientInterface
{
    public function get(string $url, array $options = []): array;

    public function post(string $url, array $data = [], array $options = []): array;
}
