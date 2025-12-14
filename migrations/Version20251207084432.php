<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251207084432 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_gardian (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, identity_id INT DEFAULT NULL, kinship ENUM(\'father\', \'mother\', \'guardianship\', \'other\') NOT NULL COMMENT \'(DC2Type:Kinship)\', kind ENUM(\'legal_gardian\', \'second_contact\') NOT NULL COMMENT \'(DC2Type:GardianKind)\', INDEX IDX_EE645A0DA76ED395 (user_id), INDEX IDX_EE645A0DFF3ED4A8 (identity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_gardian ADD CONSTRAINT FK_EE645A0DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_gardian ADD CONSTRAINT FK_EE645A0DFF3ED4A8 FOREIGN KEY (identity_id) REFERENCES identity (id)');
        $this->addSql('ALTER TABLE health ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE health ADD CONSTRAINT FK_CEDA2313A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CEDA2313A76ED395 ON health (user_id)');
        $this->addSql('ALTER TABLE identity DROP FOREIGN KEY FK_6A95E9C4A76ED395');
        $this->addSql('ALTER TABLE identity DROP INDEX IDX_6A95E9C4A76ED395');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649A08E947C');
        $this->addSql('DROP INDEX UNIQ_8D93D649A08E947C ON user');
    }

    public function postUp(Schema $schema): void
    {
        $this->connection->executeQuery('UPDATE health AS h JOIN user AS u ON u.health_id = h.id SET h.user_id = u.id');
        $this->connection->executeQuery('INSERT INTO user_gardian (user_id, identity_id, kinship, kind) SELECT user_id, id, kinship, \'legal_gardian\' FROM identity WHERE kind=:kind', ['kind' => 'kinship']);
        $this->connection->executeQuery('INSERT INTO user_gardian (user_id, identity_id, kinship, kind) SELECT user_id, id, kinship, \'second_contact\' FROM identity WHERE kind=:kind', ['kind' => 'second_contact']);
        $this->connection->executeQuery('UPDATE identity SET user_id=null WHERE kind!=\'member\'');

        $this->connection->executeQuery('ALTER TABLE user DROP health_id');
        $this->connection->executeQuery('ALTER TABLE identity DROP kinship, DROP kind');
        $this->connection->executeQuery('ALTER TABLE identity ADD CONSTRAINT FK_6A95E9C4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->connection->executeQuery('CREATE UNIQUE INDEX UNIQ_6A95E9C4A76ED395 ON identity (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_gardian DROP FOREIGN KEY FK_EE645A0DA76ED395');
        $this->addSql('ALTER TABLE user_gardian DROP FOREIGN KEY FK_EE645A0DFF3ED4A8');
        $this->addSql('DROP TABLE user_gardian');
        $this->addSql('ALTER TABLE health DROP FOREIGN KEY FK_CEDA2313A76ED395');
        $this->addSql('DROP INDEX UNIQ_CEDA2313A76ED395 ON health');
        $this->addSql('ALTER TABLE health DROP user_id');
        $this->addSql('ALTER TABLE identity DROP INDEX UNIQ_6A95E9C4A76ED395, ADD INDEX IDX_6A95E9C4A76ED395 (user_id)');
        $this->addSql('ALTER TABLE identity ADD kinship INT DEFAULT NULL, ADD kind ENUM(\'member\', \'kinship\', \'second_contact\') DEFAULT \'member\' NOT NULL COMMENT \'(DC2Type:IdentityKind)\'');
        $this->addSql('ALTER TABLE user ADD health_id INT DEFAULT NULL, CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649A08E947C FOREIGN KEY (health_id) REFERENCES health (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649A08E947C ON user (health_id)');
    }
}
