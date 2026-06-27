<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260627093448 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE second_hand DROP FOREIGN KEY FK_A325FA2112469DE2');
        $this->addSql('CREATE TABLE second_hand_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, deleted TINYINT(1) DEFAULT 0 NOT NULL, icon VARCHAR(30) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

    }

    public function postUp(Schema $schema): void
    {
        $this->connection->executeQuery('INSERT INTO second_hand_category (id, name, deleted, icon) SELECT id, name, deleted, \'\' FROM category');
        $this->connection->executeQuery('ALTER TABLE second_hand ADD CONSTRAINT FK_A325FA2112469DE2 FOREIGN KEY (category_id) REFERENCES second_hand_category (id)');
        $this->connection->executeQuery('DROP TABLE category');

        $categories = [
            ['name' => 'Vélo', 'icon' => 'lucide:bike'],
            ['name' => 'Composants', 'icon' => 'lucide:cog'],
            ['name' => 'Accessoires', 'icon' => 'lucide:package'],
            ['name' => 'Vêtements', 'icon' => 'lucide:shirt'],
        ];
        foreach ($categories as $category) {
            $this->connection->executeQuery('UPDATE second_hand_category SET icon=:icon WHERE name=:name', $category);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE second_hand DROP FOREIGN KEY FK_A325FA2112469DE2');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, deleted TINYINT(1) DEFAULT 0 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    }

    public function postDown(Schema $schema): void
    {
        $this->connection->executeQuery('INSERT INTO category (id, name, deleted) SELECT id, name, deleted FROM second_hand_category');
        $this->connection->executeQuery('ALTER TABLE second_hand ADD CONSTRAINT FK_A325FA2112469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->connection->executeQuery('DROP TABLE second_hand_category');
    }
}
