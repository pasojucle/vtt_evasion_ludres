<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210720194347 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cluster ADD role VARCHAR(25) DEFAULT NULL');
        $this->addSql('UPDATE `cluster` SET `role` = "ROLE_USER" WHERE `level_id` IS NOT NULL');
        $schoolEvents = $this->connection->fetchAllAssociative('SELECT * FROM `event` WHERE `type`=2');
        if (!empty($schoolEvents)) {
            foreach ($schoolEvents as $event) {
                $this->addSql('INSERT INTO `cluster`(`event_id`, `title`, `level_id`, `max_users`, `role`) VALUES (:id, \'Encadrement\', null, null, \'ROLE_ACCOMPANIST\')', $event);
            }
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cluster DROP role');
    }
}
