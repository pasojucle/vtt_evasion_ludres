<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260307144242 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product ADD category_tmp VARCHAR(255) DEFAULT \'undefined\' NOT NULL');
    }

    public function postUp(Schema $schema): void
    {
        foreach ($this->getCategories() as $category => $categoryEnum) {
            $this->connection->executeQuery('UPDATE product SET category_tmp=:categoryEnum WHERE category=:category',[
                'category' => $category,
                'categoryEnum' => $categoryEnum,
            ]);
        }
        $this->connection->executeQuery('ALTER TABLE product DROP category');
        $this->connection->executeQuery('ALTER TABLE product CHANGE category_tmp category VARCHAR(255) DEFAULT \'undefined\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product ADD category_tmp INT DEFAULT NULL');
    }

    public function postDown(Schema $schema): void
    {
        foreach ($this->getCategories() as $category => $categoryEnum) {
            $this->connection->executeQuery('UPDATE product SET category_tmp=:category WHERE category=:categoryEnum',[
                'category' => $category,
                'categoryEnum' => $categoryEnum,
            ]);
        }
        $this->connection->executeQuery('ALTER TABLE product DROP category');
        $this->connection->executeQuery('ALTER TABLE product CHANGE category_tmp category INT DEFAULT NULL');
    }

    private function getCategories(): array
    {
        return [
            1 => 'school',
            2 => 'adult',
            3 => 'school_and_adult'
        ];
    }
}
