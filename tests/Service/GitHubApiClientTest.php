<?php

namespace App\Tests\Service;

use App\Service\GitHubApiClient;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class GitHubApiClientTest extends TestCase
{
    public function testSearchMostStarredPhpProjectsReturnsItems(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('toArray')
            ->with(false)
            ->willReturn([
                'items' => [
                    [
                        'full_name' => 'symfony/symfony',
                        'html_url' => 'https://github.com/symfony/symfony',
                        'description' => 'The Symfony PHP framework',
                        'stargazers_count' => 100000,
                        'language' => 'PHP',
                        'owner' => [
                            'login' => 'symfony',
                            'html_url' => 'https://github.com/symfony',
                        ],
                    ],
                ],
            ]);

        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://api.github.com/search/repositories',
                $this->callback(fn ($options) =>
                    isset($options['query'])
                    && $options['query']['q'] === 'language:php'
                    && $options['query']['sort'] === 'stars'
                    && $options['query']['order'] === 'desc'
                )
            )
            ->willReturn($response);

        $api = new GitHubApiClient($client);
        $results = $api->searchMostStarredPhpProjects(1);

        $this->assertCount(1, $results);
        $this->assertSame('symfony/symfony', $results[0]['full_name']);
    }
}
