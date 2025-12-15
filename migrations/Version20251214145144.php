<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251214145144 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE agreement ADD order_by INT DEFAULT NULL, ADD enabled TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE background CHANGE landscape_position landscape_position JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE square_position square_position JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE portrait_position portrait_position JSON NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE bike_ride CHANGE level_filter level_filter JSON DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE bike_ride_type CHANGE clusters clusters JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE registration registration ENUM(\'none\', \'school\', \'cluster\') DEFAULT \'none\' NOT NULL COMMENT \'(DC2Type:Registration)\'');
        $this->addSql('ALTER TABLE documentation CHANGE name name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE second_hand_image RENAME INDEX idx_3119eb94fa993434 TO IDX_CDA3CA99FA993434');
        $this->addSql('ALTER TABLE survey CHANGE level_filter level_filter JSON DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function postUp(Schema $schema): void
    {
        foreach($this->getAgreements() as $agreement) {
            $this->connection->executeQuery('UPDATE agreement SET order_by=:orderBy WHERE id=:id', $agreement);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bike_ride_type CHANGE clusters clusters JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE registration registration VARCHAR(255) DEFAULT \'none\' NOT NULL COMMENT \'(DC2Type:Registration\'');
        $this->addSql('ALTER TABLE bike_ride CHANGE level_filter level_filter JSON DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE agreement DROP order_by, DROP enabled');
        $this->addSql('ALTER TABLE survey CHANGE level_filter level_filter JSON DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE documentation CHANGE name name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE second_hand_image RENAME INDEX idx_cda3ca99fa993434 TO IDX_3119EB94FA993434');
        $this->addSql('ALTER TABLE background CHANGE landscape_position landscape_position JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE square_position square_position JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE portrait_position portrait_position JSON NOT NULL COMMENT \'(DC2Type:json)\'');
    }

    private function getAgreements(): array
    {
        return [
            ['id' => 'BACK_HOME_ALONE', 'orderBy' => 3],
            ['id' => 'EMERGENCY_CARE_ADULT', 'orderBy' => 4],
            ['id' => 'EMERGENCY_CARE_SCHOOL', 'orderBy' => 5],
            ['id' => 'HEALTH_ADULT', 'orderBy' => 6],
            ['id' => 'HEALTH_ADULT_2', 'orderBy' => 7],
            ['id' => 'HEALTH_SCHOOL', 'orderBy' => 8],
            ['id' => 'HEALTH_SCHOOL_2', 'orderBy' => 9],
            ['id' => 'IMAGE_USE_ADULT', 'orderBy' => 1],
            ['id' => 'IMAGE_USE_SCHOOL', 'orderBy' => 2],
            ['id' => 'PARENTAL_CONSENT', 'orderBy' => 0],
            ['id' => 'RULES', 'orderBy' => 10],
        ];
    }
}
