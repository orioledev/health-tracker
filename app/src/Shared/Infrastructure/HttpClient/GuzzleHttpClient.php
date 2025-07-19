<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\HttpClient;

use App\Shared\Application\HttpClient\HttpClientInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

class GuzzleHttpClient implements HttpClientInterface
{
    private GuzzleClient $client;

    public function __construct(GuzzleClient $client)
    {
        $this->client = $client;
    }

    public function get(string $url, array $options = []): array
    {
        try {
            $response = $this->client->get($url, $options);
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new RuntimeException('HTTP request failed: ' . $e->getMessage());
        }
    }

    public function post(string $url, array $data = [], array $options = []): array
    {
        try {
            $options['json'] = $data;
            $response = $this->client->post($url, $options);
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new RuntimeException('HTTP request failed: ' . $e->getMessage());
        }
    }
}
