<?php

namespace App\Entity;

use App\Repository\GitHubPhpProjectRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GitHubPhpProjectRepository::class)]
#[ORM\Table(name: 'github_php_projects')]
class GitHubPhpProject
{
    #[ORM\Id]
    #[ORM\Column(type: Types::BIGINT, options: ['unsigned' => true])]
    private ?int $repoId = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $lastPushDate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::INTEGER, options: ['unsigned' => true])]
    private ?int $stars = null;

    public function getRepoId(): ?int
    {
        return $this->repoId;
    }

    public function setRepoId(int $repoId): static
    {
        $this->repoId = $repoId;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeInterface $createdDate): static
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    public function getLastPushDate(): ?\DateTimeInterface
    {
        return $this->lastPushDate;
    }

    public function setLastPushDate(\DateTimeInterface $lastPushDate): static
    {
        $this->lastPushDate = $lastPushDate;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStars(): ?int
    {
        return $this->stars;
    }

    public function setStars(int $stars): static
    {
        $this->stars = $stars;

        return $this;
    }
}
