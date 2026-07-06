<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use DateTime;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260703173148 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bike_ride ADD deleted_by_id INT DEFAULT NULL, ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE bike_ride ADD CONSTRAINT FK_8A8A7B3CC76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_8A8A7B3CC76F1F52 ON bike_ride (deleted_by_id)');
        $this->addSql('ALTER TABLE bike_ride_type ADD deleted_by_id INT DEFAULT NULL, ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE bike_ride_type ADD CONSTRAINT FK_24DABBBFC76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_24DABBBFC76F1F52 ON bike_ride_type (deleted_by_id)');
        $this->addSql('ALTER TABLE bike_ride_type_message DROP FOREIGN KEY FK_68B5B8DE537A1329');
        $this->addSql('ALTER TABLE bike_ride_type_message ADD CONSTRAINT FK_68B5B8DE537A1329 FOREIGN KEY (message_id) REFERENCES message (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE level ADD deleted_by_id INT DEFAULT NULL, ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE level ADD CONSTRAINT FK_9AEACC13C76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_9AEACC13C76F1F52 ON level (deleted_by_id)');
        $this->addSql('ALTER TABLE parameter CHANGE id id VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE product ADD deleted_by_id INT DEFAULT NULL, ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADC76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_D34A04ADC76F1F52 ON product (deleted_by_id)');
        $this->addSql('ALTER TABLE second_hand_category ADD deleted_by_id INT DEFAULT NULL, ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE second_hand_category ADD CONSTRAINT FK_D1A0B7B8C76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_D1A0B7B8C76F1F52 ON second_hand_category (deleted_by_id)');
        $this->addSql('ALTER TABLE session CHANGE practice practice VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE skill ADD deleted_by_id INT DEFAULT NULL, ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE skill ADD CONSTRAINT FK_5E3DE477C76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_5E3DE477C76F1F52 ON skill (deleted_by_id)');
        $this->addSql('ALTER TABLE skill_category ADD deleted_by_id INT DEFAULT NULL, ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE skill_category ADD CONSTRAINT FK_44E47433C76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_44E47433C76F1F52 ON skill_category (deleted_by_id)');
        $this->addSql('ALTER TABLE board_role ADD deleted_by_id INT DEFAULT NULL, ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE board_role ADD CONSTRAINT FK_8DFFD349C76F1F52 FOREIGN KEY (deleted_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_8DFFD349C76F1F52 ON board_role (deleted_by_id)');
    }

    public function postUp(Schema $schema): void
    {
        $this->connection->executeQuery('UPDATE bike_ride SET deleted_at=NOW() WHERE deleted=1');
        $this->connection->executeQuery('ALTER TABLE bike_ride DROP deleted');

        $this->connection->executeQuery('UPDATE level SET deleted_at=NOW() WHERE is_deleted=1');
        $this->connection->executeQuery('ALTER TABLE level DROP is_deleted');

        $this->connection->executeQuery('UPDATE product SET deleted_at=NOW() WHERE deleted=1');
        $this->connection->executeQuery('ALTER TABLE product DROP deleted');

        $this->connection->executeQuery('UPDATE second_hand_category SET deleted_at=NOW() WHERE deleted=1');
        $this->connection->executeQuery('ALTER TABLE second_hand_category DROP deleted');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE second_hand_category DROP FOREIGN KEY FK_D1A0B7B8C76F1F52');
        $this->addSql('DROP INDEX IDX_D1A0B7B8C76F1F52 ON second_hand_category');
        $this->addSql('ALTER TABLE second_hand_category ADD deleted TINYINT(1) DEFAULT 0 NOT NULL, DROP deleted_by_id');
        $this->addSql('ALTER TABLE bike_ride_type DROP FOREIGN KEY FK_24DABBBFC76F1F52');
        $this->addSql('DROP INDEX IDX_24DABBBFC76F1F52 ON bike_ride_type');
        $this->addSql('ALTER TABLE bike_ride_type DROP deleted_by_id, DROP deleted_at');
        $this->addSql('ALTER TABLE bike_ride DROP FOREIGN KEY FK_8A8A7B3CC76F1F52');
        $this->addSql('DROP INDEX IDX_8A8A7B3CC76F1F52 ON bike_ride');
        $this->addSql('ALTER TABLE bike_ride ADD deleted TINYINT(1) DEFAULT 0 NOT NULL, DROP deleted_by_id');
        $this->addSql('ALTER TABLE bike_ride_type_message DROP FOREIGN KEY FK_68B5B8DE537A1329');
        $this->addSql('ALTER TABLE bike_ride_type_message ADD CONSTRAINT FK_68B5B8DE537A1329 FOREIGN KEY (message_id) REFERENCES message (id)');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADC76F1F52');
        $this->addSql('DROP INDEX IDX_D34A04ADC76F1F52 ON product');
        $this->addSql('ALTER TABLE product ADD deleted TINYINT(1) DEFAULT 0 NOT NULL, DROP deleted_by_id');
        $this->addSql('ALTER TABLE skill_category DROP FOREIGN KEY FK_44E47433C76F1F52');
        $this->addSql('DROP INDEX IDX_44E47433C76F1F52 ON skill_category');
        $this->addSql('ALTER TABLE skill_category DROP deleted_by_id, DROP deleted_at');
        $this->addSql('ALTER TABLE skill DROP FOREIGN KEY FK_5E3DE477C76F1F52');
        $this->addSql('DROP INDEX IDX_5E3DE477C76F1F52 ON skill');
        $this->addSql('ALTER TABLE skill DROP deleted_by_id, DROP deleted_at');
        $this->addSql('ALTER TABLE level DROP FOREIGN KEY FK_9AEACC13C76F1F52');
        $this->addSql('DROP INDEX IDX_9AEACC13C76F1F52 ON level');
        $this->addSql('ALTER TABLE level ADD is_deleted TINYINT(1) NOT NULL, DROP deleted_by_id');
        $this->addSql('ALTER TABLE session CHANGE practice practice ENUM(\'vtt\', \'vttae\', \'roadbike\', \'gravel\', \'gravelae\', \'walking\', \'none\') NOT NULL COMMENT \'(DC2Type:Practice)\'');
        $this->addSql('ALTER TABLE parameter CHANGE id id VARCHAR(150) NOT NULL');
        $this->addSql('ALTER TABLE board_role DROP FOREIGN KEY FK_8DFFD349C76F1F52');
        $this->addSql('DROP INDEX IDX_8DFFD349C76F1F52 ON board_role');
        $this->addSql('ALTER TABLE board_role DROP deleted_by_id, DROP deleted_at');
    }

        public function postDown(Schema $schema): void
    {
        $this->connection->executeQuery('UPDATE bike_ride SET deleted = 1 WHERE deleted_at IS NOT NULL');
        $this->connection->executeQuery('ALTER TABLE bike_ride DROP deleted_at');

        $this->connection->executeQuery('UPDATE level SET is_deleted = 1 WHERE deleted_at IS NOT NULL');
        $this->connection->executeQuery('ALTER TABLE level DROP deleted_at');
        
        $this->connection->executeQuery('UPDATE product SET deleted = 1 WHERE deleted_at IS NOT NULL');
        $this->connection->executeQuery('ALTER TABLE product DROP deleted_at');

        $this->connection->executeQuery('UPDATE second_hand_category SET deleted = 1 WHERE deleted_at IS NOT NULL');
        $this->connection->executeQuery('ALTER TABLE second_hand_category DROP deleted_at');
    }
}
