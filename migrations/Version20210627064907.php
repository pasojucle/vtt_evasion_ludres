<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210627064907 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cluster (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event ADD users_per_cluster INT DEFAULT NULL');
        $this->addSql('ALTER TABLE session ADD cluster_id INT NOT NULL');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D4C36A3328 FOREIGN KEY (cluster_id) REFERENCES cluster (id)');
        $this->addSql('CREATE INDEX IDX_D044D5D4C36A3328 ON session (cluster_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D4C36A3328');
        $this->addSql('DROP TABLE cluster');
        $this->addSql('ALTER TABLE event DROP users_per_cluster');
        $this->addSql('DROP INDEX IDX_D044D5D4C36A3328 ON session');
        $this->addSql('ALTER TABLE session DROP cluster_id');
    }
}
