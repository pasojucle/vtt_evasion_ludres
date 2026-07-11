<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260711094530 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE content ADD disabled_by_id INT DEFAULT NULL, ADD disabled_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE content ADD CONSTRAINT FK_FEC530A91688BE50 FOREIGN KEY (disabled_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_FEC530A91688BE50 ON content (disabled_by_id)');
        $this->addSql('ALTER TABLE product ADD disabled_by_id INT DEFAULT NULL, ADD disabled_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD1688BE50 FOREIGN KEY (disabled_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_D34A04AD1688BE50 ON product (disabled_by_id)');
        $this->addSql('ALTER TABLE survey ADD disabled_by_id INT DEFAULT NULL, ADD disabled_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE survey ADD CONSTRAINT FK_AD5F9BFC1688BE50 FOREIGN KEY (disabled_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_AD5F9BFC1688BE50 ON survey (disabled_by_id)');

        $this->addSql('ALTER TABLE notification ADD disabled_by_id INT DEFAULT NULL, ADD disabled_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA1688BE50 FOREIGN KEY (disabled_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_BF5476CA1688BE50 ON notification (disabled_by_id)');
    }

    public function postUp(Schema $schema): void
    {
        $this->connection->executeQuery('UPDATE content SET disabled_at=NOW() WHERE is_active=0');
        $this->connection->executeQuery('UPDATE product SET disabled_at=NOW() WHERE is_disabled=1');
        $this->connection->executeQuery('UPDATE survey SET disabled_at=NOW() WHERE disabled=1');
        $this->connection->executeQuery('UPDATE notification SET disabled_at=NOW() WHERE is_disabled=1');

        $this->connection->executeQuery('ALTER TABLE content DROP is_active');
        $this->connection->executeQuery('ALTER TABLE product DROP is_disabled');
        $this->connection->executeQuery('ALTER TABLE survey DROP disabled');
        $this->connection->executeQuery('ALTER TABLE notification DROP is_disabled');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE content DROP FOREIGN KEY FK_FEC530A91688BE50');
        $this->addSql('DROP INDEX IDX_FEC530A91688BE50 ON content');
        $this->addSql('ALTER TABLE content ADD is_active TINYINT(1) DEFAULT 1 NOT NULL, DROP disabled_by_id');
        $this->addSql('ALTER TABLE survey DROP FOREIGN KEY FK_AD5F9BFC1688BE50');
        $this->addSql('DROP INDEX IDX_AD5F9BFC1688BE50 ON survey');
        $this->addSql('ALTER TABLE survey ADD disabled TINYINT(1) DEFAULT 0 NOT NULL, DROP disabled_by_id');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD1688BE50');
        $this->addSql('DROP INDEX IDX_D34A04AD1688BE50 ON product');
        $this->addSql('ALTER TABLE product ADD is_disabled TINYINT(1) DEFAULT 0 NOT NULL, DROP disabled_by_id');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA1688BE50');
        $this->addSql('DROP INDEX IDX_BF5476CA1688BE50 ON notification');
        $this->addSql('ALTER TABLE notification ADD is_disabled TINYINT(1) DEFAULT 0 NOT NULL, DROP disabled_by_id, DROP disabled_at');
    }

    public function postDown(Schema $schema): void
    {
        $this->connection->executeQuery('UPDATE content SET is_active=0 WHERE disabled_at IS NOT NULL');
        $this->connection->executeQuery('UPDATE product SET is_disabled=1 WHERE disabled_at IS NOT NULL');
        $this->connection->executeQuery('UPDATE survey SET disabled=1 WHERE disabled_at IS NOT NULL');
        $this->connection->executeQuery('UPDATE notification SET is_disabled=1 WHERE disabled_at IS NOT NULL');

        $this->connection->executeQuery('ALTER TABLE content DROP disabled_at');
        $this->connection->executeQuery('ALTER TABLE survey DROP disabled_at');
        $this->connection->executeQuery('ALTER TABLE product DROP disabled_at');
        $this->connection->executeQuery('ALTER TABLE notification DROP disabled_at');
    }
}
