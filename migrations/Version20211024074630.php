<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211024074630 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('TRUNCATE TABLE order_line');
        $this->addSql('DELETE FROM order_line');
        $this->addSql('ALTER TABLE order_line AUTO_INCREMENT = 1');
        $this->addSql('DELETE FROM order_header');
        $this->addSql('ALTER TABLE order_header AUTO_INCREMENT = 1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
