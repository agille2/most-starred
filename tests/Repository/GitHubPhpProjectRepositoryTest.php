<?php 

namespace App\Tests\Repository;

use App\Entity\GitHubPhpProject;
use App\Repository\GitHubPhpProjectRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GitHubPhpProjectRepositoryTest extends KernelTestCase
{
    private ?GitHubPhpProjectRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->repository = self::getContainer()->get(GitHubPhpProjectRepository::class);
    }

    public function testUpsertHandlesNewAndExistingRecords(): void
    {
        //Mock payload containing one target record
        $payload = [[
            'id' => 12345,
            'full_name' => 'symfony/symfony',
            'html_url' => 'https://github.com',
            'created_at' => '2011-05-20T12:00:00Z',
            'pushed_at' => '2026-05-20T09:00:00Z',
            'description' => 'A PHP framework',
            'stargazers_count' => 30000
        ]];

        //Insert as a brand-new entity
        $insertedCount = $this->repository->upsertFromGitHubSearchResults($payload);
        $this->assertSame(1, $insertedCount);

        // Verify entity database population
        $savedRepo = $this->repository->find(12345);
        $this->assertNotNull($savedRepo);
        $this->assertSame('symfony/symfony', $savedRepo->getName());
        $this->assertSame(30000, $savedRepo->getStars());

        //Update the same record with new modifications
        $payload[0]['stargazers_count'] = 30005;
        $payload[0]['full_name'] = 'symfony/symfony-updated';
        
        $updatedCount = $this->repository->upsertFromGitHubSearchResults($payload);
        $this->assertSame(1, $updatedCount);

        //Confirm it was an update (upsert) and did not duplicate entries
        $updatedRepo = $this->repository->find(12345);
        $this->assertSame('symfony/symfony-updated', $updatedRepo->getName());
        $this->assertSame(30005, $updatedRepo->getStars());
    }

    public function testUpsertSkipsMalformedPayloadItems(): void
    {
        // Payload entry missing mandatory 'stargazers_count'
        $invalidPayload = [[
            'id' => 99999,
            'full_name' => 'broken/repo',
            'html_url' => 'https://github.com',
            'created_at' => '2026-01-01T00:00:00Z',
            'pushed_at' => '2026-01-01T00:00:00Z',
        ]];

        $count = $this->repository->upsertFromGitHubSearchResults($invalidPayload);
        
        $this->assertSame(0, $count);
        $this->assertNull($this->repository->find(99999));
    }
}