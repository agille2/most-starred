<?php

namespace App\Repository;

use App\Entity\GitHubPhpProject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GitHubPhpProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GitHubPhpProject::class);
    }

    /**
     * @return GitHubPhpProject[]
     */
    public function findAllOrderedByStars(): array
    {
        return $this->findBy([], ['stars' => 'DESC']);
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    public function upsertFromGitHubSearchResults(array $items, bool $flush = true): int
    {
        $em = $this->getEntityManager();
        $count = 0;

        foreach ($items as $item) {
            if (!isset($item['id'], $item['full_name'], $item['html_url'], $item['created_at'], $item['pushed_at'], $item['stargazers_count'])) {
                continue;
            }

            $repo = $this->find((int) $item['id']) ?? new GitHubPhpProject();

            if ($repo->getRepoId() === null) {
                $repo->setRepoId((int) $item['id']);
            }

            $repo->setName((string) $item['full_name']);
            $repo->setUrl((string) $item['html_url']);
            $repo->setCreatedDate(new \DateTimeImmutable((string) $item['created_at']));
            $repo->setLastPushDate(new \DateTimeImmutable((string) $item['pushed_at']));
            $repo->setDescription(isset($item['description']) ? (string) $item['description'] : null);
            $repo->setStars((int) $item['stargazers_count']);

            $em->persist($repo);
            $count++;
        }

        if ($flush) {
            $em->flush();
        }

        return $count;
    }

    public function findPaginatedForDataTables(int $offset, int $limit, string $search, string $sortField = 'stars', string $sortDir = 'desc'): array 
    {
        $qb = $this->createQueryBuilder('p')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        // Sanitize column inputs to prevent SQL injection
        $allowedFields = ['id', 'name', 'stars'];
        $sortField = in_array($sortField, $allowedFields, true) ? $sortField : 'stars';

        $sortDir = strtoupper($sortDir) === 'ASC' ? 'ASC' : 'DESC';

        $qb->orderBy('p.' . $sortField, $sortDir);

        if (!empty($search)) {
            $qb->andWhere('p.name LIKE :search')
            ->setParameter('search', '%' . $search . '%');
        }

        return $qb->getQuery()->getResult();
    }

    public function countFilteredForDataTables(string $search): int
    {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(p.repoId)');

        if (!empty($search)) {
            $qb->andWhere('p.name LIKE :search')
            ->setParameter('search', '%' . $search . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
