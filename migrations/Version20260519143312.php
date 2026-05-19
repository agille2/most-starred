<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260519143312 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create github_php_projects table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE github_php_projects (
            repo_id BIGINT UNSIGNED NOT NULL,
            name VARCHAR(255) NOT NULL,
            url VARCHAR(255) NOT NULL,
            created_date DATETIME NOT NULL,
            last_push_date DATETIME NOT NULL,
            description LONGTEXT DEFAULT NULL,
            stars INT UNSIGNED NOT NULL,
            PRIMARY KEY(repo_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE github_php_projects');
    }
}
