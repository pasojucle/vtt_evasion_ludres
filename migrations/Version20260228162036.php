<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260228162036 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bike_ride_type ADD public TINYINT(1) DEFAULT 0 NOT NULL, ADD require_disponibility TINYINT(1) DEFAULT 0 NOT NULL, ADD notify TINYINT(1) DEFAULT 0 NOT NULL, CHANGE clusters clusters JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE registration registration ENUM(\'none\', \'school\', \'cluster\') DEFAULT \'none\' NOT NULL COMMENT \'(DC2Type:Registration)\'');
    }

    public function postUp(Schema $schema): void
    {
        $this->connection->executeQuery('INSERT INTO `bike_ride_type`(`name`, `content`, `is_compensable`, `show_member_list`, `use_levels`, `clusters`, `need_framers`, `closing_duration`, `display_bike_kind`, `registration`, `public`, `require_disponibility`, `notify`) VALUES (:name, :content, :isCompensable, :showMemberList, :useLevels, :clusters, :needFramers, :closingDuration, :displayBikeKind, :registration, :public, :requireDisponibility, :notify)', $this->getPublicBikeRideType());
        $this->connection->executeQuery('UPDATE `bike_ride_type` SET require_disponibility=1, notify=1 WHERE name=:name', ['name' =>'Réunion du club']);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bike_ride_type DROP public, DROP require_disponibility, DROP notify, CHANGE clusters clusters JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE registration registration VARCHAR(255) DEFAULT \'none\' NOT NULL COMMENT \'(DC2Type:Registration\'');
    }

    public function postDown(Schema $schema): void
    {
        $this->connection->executeQuery('DELETE FROM `bike_ride_type` WHERE name=:name', $this->getPublicBikeRideType());
    }

    private function getPublicBikeRideType(): array
    {
        return [
            'name' => 'Randonnée du club', 
            'content' => '<p>Randonnée VTT & marche</p><ul class="list-outside"><li>Parcours VTT sportif et familial</li><li>Parcours marche</li><li>Parcours gravel</li></ul>', 
            'isCompensable' => 0, 
            'showMemberList' => 0, 
            'useLevels' => 0, 
            'clusters' => json_encode(["VTT", "Gravel", "Marche"]), 
            'needFramers' => 0, 
            'closingDuration' => 0, 
            'displayBikeKind' => 0, 
            'registration' => 'cluster',
            'public' => 1,
            'requireDisponibility' => 0,
            'notify' => 0,
        ];
    }
}
