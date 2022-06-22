<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220611103546 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE background (id INT AUTO_INCREMENT NOT NULL, filename VARCHAR(255) NOT NULL, landscape_position LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', square_position LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE content_background (content_id INT NOT NULL, background_id INT NOT NULL, INDEX IDX_255102A284A0A3ED (content_id), INDEX IDX_255102A2C93D69EA (background_id), PRIMARY KEY(content_id, background_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE content_background ADD CONSTRAINT FK_255102A284A0A3ED FOREIGN KEY (content_id) REFERENCES content (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE content_background ADD CONSTRAINT FK_255102A2C93D69EA FOREIGN KEY (background_id) REFERENCES background (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE content_background DROP FOREIGN KEY FK_255102A2C93D69EA');
        $this->addSql('DROP TABLE background');
        $this->addSql('DROP TABLE content_background');
    }
}
