<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250518152354 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE parameter MODIFY id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `primary` ON parameter
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE parameter ADD position INT NOT NULL, ADD kind ENUM('choice', 'bool', 'text', 'image') NOT NULL COMMENT '(DC2Type:ParameterKind)', CHANGE options options JSON DEFAULT NULL COMMENT '(DC2Type:json)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE parameter ADD PRIMARY KEY (name)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD roles_tmp JSON NOT NULL COMMENT '(DC2Type:json)', CHANGE password password VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user RENAME INDEX uniq_8d93d649e7927c74 TO UNIQ_IDENTIFIER_EMAIL
        SQL);
    }

    public function postUp(Schema $schema): void
    {
        $this->connection->executeQuery('UPDATE parameter set kind = type, position = order_by');
        $this->connection->executeQuery('ALTER TABLE parameter DROP id, DROP type, DROP order_by');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

        $this->addSql(<<<'SQL'
            ALTER TABLE user CHANGE roles roles JSON NOT NULL COMMENT '(DC2Type:json)', CHANGE password password VARCHAR(255) DEFAULT NULL, CHANGE is_active is_active TINYINT(1) DEFAULT 0 NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user RENAME INDEX uniq_identifier_email TO UNIQ_8D93D649E7927C74
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE parameter ADD id INT AUTO_INCREMENT NOT NULL, ADD type VARCHAR(50) NOT NULL, ADD order_by INT DEFAULT NULL, DROP position, DROP kind, CHANGE value value VARCHAR(50) DEFAULT NULL, CHANGE options options JSON DEFAULT NULL COMMENT '(DC2Type:json)', DROP PRIMARY KEY, ADD PRIMARY KEY (id)
        SQL);
    }
}
