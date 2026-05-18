<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class GitHubApiClient
{
    private HttpClientInterface $client;
    private ?string $token;

    public function __construct(HttpClientInterface $client, ?string $githubToken = null)
    {
        $this->client = $client;
        $this->token = $githubToken ?: getenv('GITHUB_TOKEN') ?: null;
    }

    /**
     * Using REST API over GraphQL as it is better for high-level aggregation tasks like this, 
     * and also to avoid complexity of GraphQL queries and potential rate limit issues.
     * @return array<int, array<string, mixed>>
     */
    public function searchMostStarredPhpProjects(int $limit = 25): array
    {
        $options = [
            'query' => [
                'q' => 'language:php',
                'sort' => 'stars',
                'order' => 'desc',
                'per_page' => $limit,
            ],
            'headers' => [
                'Accept' => 'application/vnd.github+json',
                'User-Agent' => 'SymfonyMostStarredApp',
            ],
        ];

        if ($this->token !== null) {
            $options['headers']['Authorization'] = 'Bearer ' . $this->token;
        }

        $response = $this->client->request('GET', 'https://api.github.com/search/repositories', $options);

        return $response->toArray(false)['items'] ?? [];
    }
}
