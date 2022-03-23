<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220319184223 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vote ADD is_anonymous TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE vote_issue CHANGE vote_id vote_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vote_response ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vote_response ADD CONSTRAINT FK_C024A2D2A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_C024A2D2A76ED395 ON vote_response (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vote DROP is_anonymous');
        $this->addSql('ALTER TABLE vote_issue CHANGE vote_id vote_id INT NOT NULL');
        $this->addSql('ALTER TABLE vote_response DROP FOREIGN KEY FK_C024A2D2A76ED395');
        $this->addSql('DROP INDEX IDX_C024A2D2A76ED395 ON vote_response');
        $this->addSql('ALTER TABLE vote_response DROP user_id');
    }
}
