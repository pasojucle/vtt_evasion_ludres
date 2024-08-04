<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240720050103 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bike_ride_type ADD registration_tmp ENUM(\'none\', \'school\', \'cluster\') DEFAULT \'none\' NOT NULL COMMENT \'(DC2Type:Registration\'');
        $this->addSql('UPDATE bike_ride_type SET registration_tmp = \'none\' WHERE registration = 0');
        $this->addSql('UPDATE bike_ride_type SET registration_tmp = \'school\' WHERE registration = 1');
        $this->addSql('UPDATE bike_ride_type SET registration_tmp = \'cluster\' WHERE registration = 2');
        $this->addSql('ALTER TABLE bike_ride_type DROP registration');
        $this->addSql('ALTER TABLE bike_ride_type CHANGE registration_tmp registration ENUM(\'none\', \'school\', \'cluster\') DEFAULT \'none\' NOT NULL COMMENT \'(DC2Type:Registration\'');

        $this->addSql('ALTER TABLE identity ADD kind ENUM(\'member\', \'kinship\', \'second_contact\') DEFAULT \'member\' NOT NULL COMMENT \'(DC2Type:IdentityKind)\'');
        $this->addSql('UPDATE identity SET kind = \'member\' WHERE type = 1');
        $this->addSql('UPDATE identity SET kind = \'kinship\' WHERE type = 2');
        $this->addSql('UPDATE identity SET kind = \'second_contact\' WHERE type = 3');
        $this->addSql('ALTER TABLE identity DROP type');

        $this->addSql('ALTER TABLE order_header ADD status_tmp  ENUM(\'in_progress\', \'ordered\', \'valided\', \'completed\', \'canceled\') NOT NULL COMMENT \'(DC2Type:order_status)\'');
        $this->addSql('UPDATE order_header SET status_tmp = \'in_progress\' WHERE status = 1');
        $this->addSql('UPDATE order_header SET status_tmp = \'ordered\' WHERE status = 2');
        $this->addSql('UPDATE order_header SET status_tmp = \'valided\' WHERE status = 3');
        $this->addSql('UPDATE order_header SET status_tmp = \'completed\' WHERE status = 4');
        $this->addSql('UPDATE order_header SET status_tmp = \'canceled\' WHERE status = 9');
        $this->addSql('ALTER TABLE order_header DROP status');
        $this->addSql('ALTER TABLE order_header CHANGE status_tmp status ENUM(\'in_progress\', \'ordered\', \'valided\', \'completed\', \'canceled\') NOT NULL COMMENT \'(DC2Type:order_status\'');
    
        $this->addSql('ALTER TABLE session ADD practice ENUM(\'vtt\', \'vttae\', \'roadbike\', \'gravel\', \'walking\') NOT NULL COMMENT \'(DC2Type:practice)\', ADD availability_tmp ENUM(\'registered\', \'available\', \'unavailable\') DEFAULT NULL COMMENT \'(DC2Type:availability)\'');
        $this->addSql('UPDATE session SET practice = \'vtt\' WHERE bike_kind = 1');
        $this->addSql('UPDATE session SET practice = \'vttae\' WHERE bike_kind = 2');
        $this->addSql('UPDATE session SET practice = \'roadbike\' WHERE bike_kind = 3');
        $this->addSql('UPDATE session SET practice = \'gravel\' WHERE bike_kind = 4');
        $this->addSql('UPDATE session SET practice = \'walking\' WHERE bike_kind = 5');
        $this->addSql('UPDATE session SET availability_tmp = \'registered\' WHERE availability = 1');
        $this->addSql('UPDATE session SET availability_tmp = \'available\' WHERE availability = 2');
        $this->addSql('UPDATE session SET availability_tmp = \'unavailable\' WHERE availability = 3');
        $this->addSql('ALTER TABLE session DROP bike_kind, DROP availability');
        $this->addSql('ALTER TABLE session CHANGE availability_tmp availability ENUM(\'registered\', \'available\', \'unavailable\') DEFAULT NULL COMMENT \'(DC2Type:availability)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bike_ride_type ADD registration_tmp INT DEFAULT 0 NOT NULL');
        $this->addSql('UPDATE bike_ride_type SET registration_tmp = 0 WHERE registration = \'none\'');
        $this->addSql('UPDATE bike_ride_type SET registration_tmp = 1 WHERE registration = \'school\' ');
        $this->addSql('UPDATE bike_ride_type SET registration_tmp = 2 WHERE registration = \'cluster\'');
        $this->addSql('ALTER TABLE bike_ride_type DROP registration');
        $this->addSql('ALTER TABLE bike_ride_type CHANGE registration_tmp registration INT DEFAULT 0 NOT NULL');

        $this->addSql('ALTER TABLE identity ADD type INT DEFAULT 1 NOT NULL');
        $this->addSql('UPDATE identity SET type = 1 WHERE kind = \'member\'');
        $this->addSql('UPDATE identity SET type = 2 WHERE kind = \'kinship\'');
        $this->addSql('UPDATE identity SET type = 3 WHERE kind = \'second_contact\'');
        $this->addSql('ALTER TABLE identity DROP kind');

        $this->addSql('ALTER TABLE order_header ADD status_tmp INT NOT NULL');
        $this->addSql('UPDATE order_header SET status_tmp = 1 WHERE status = \'in_progress\'');
        $this->addSql('UPDATE order_header SET status_tmp = 2 WHERE status = \'ordered\'');
        $this->addSql('UPDATE order_header SET status_tmp = 3 WHERE status = \'valided\'');
        $this->addSql('UPDATE order_header SET status_tmp = 4 WHERE status = \'completed\'');
        $this->addSql('UPDATE order_header SET status_tmp = 9 WHERE status = \'canceled\'');
        $this->addSql('ALTER TABLE order_header DROP status');
        $this->addSql('ALTER TABLE order_header CHANGE status_tmp status INT NOT NULL');

        $this->addSql('ALTER TABLE session ADD bike_kind INT DEFAULT NULL, ADD availability_tmp INT DEFAULT NULL');
        $this->addSql('UPDATE session SET bike_kind = 1 WHERE practice = \'vtt\'');
        $this->addSql('UPDATE session SET bike_kind = 2 WHERE practice = \'vttae\'');
        $this->addSql('UPDATE session SET bike_kind = 3 WHERE practice = \'roadbike\'');
        $this->addSql('UPDATE session SET bike_kind = 4 WHERE practice = \'gravel\'');
        $this->addSql('UPDATE session SET bike_kind = 5 WHERE practice = \'walking\'');
        $this->addSql('UPDATE session SET availability_tmp = 1 WHERE availability = \'registered\'');
        $this->addSql('UPDATE session SET availability_tmp = 2 WHERE availability = \'available\'');
        $this->addSql('UPDATE session SET availability_tmp = 3 WHERE availability =\'unavailable\' ');
        $this->addSql('ALTER TABLE session DROP bike_kind, DROP availability');
        $this->addSql('ALTER TABLE session DROP practice, DROP availability');
        $this->addSql('ALTER TABLE session CHANGE availability_tmp availability INT DEFAULT NULL');
    }
}
