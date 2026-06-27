<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260624173203 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FD823E37A');
        $this->addSql('ALTER TABLE parameter DROP FOREIGN KEY FK_2A979110132604DB');
        $this->addSql('ALTER TABLE bike_ride_type_message DROP FOREIGN KEY FK_68B5B8DE537A1329');

        $this->addSql('CREATE TABLE section (id VARCHAR(100) NOT NULL, label VARCHAR(255) NOT NULL, role VARCHAR(25) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE bike_ride_type_message CHANGE message_id message_id VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE message CHANGE id id VARCHAR(100) NOT NULL, CHANGE section_id section_id VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE parameter CHANGE id id VARCHAR(100) NOT NULL, ADD section_id VARCHAR(100) DEFAULT NULL'); 
        
        $this->addSql('DROP INDEX IDX_2A979110132604DB ON parameter');
    }

    public function postUp(Schema $schema): void
    {
        $this->connection->executeQuery('INSERT INTO section (id, label, role) SELECT name, label, role FROM parameter_group');
        $this->connection->executeQuery('UPDATE parameter p INNER JOIN parameter_group pg ON pg.id = p.parameter_group_id SET p.section_id = pg.name');
        $this->connection->executeQuery('UPDATE message m INNER JOIN parameter_group pg ON pg.id = m.section_id SET m.section_id = pg.name');

        $this->connection->executeQuery('UPDATE bike_ride_type_message brt INNER JOIN message m ON m.id = brt.message_id SET brt.message_id = m.name');
        $this->connection->executeQuery('UPDATE message SET id = name');
        $this->connection->executeQuery('UPDATE parameter SET id = name');

        $this->connection->executeQuery('DROP TABLE parameter_group');
        $this->connection->executeQuery('ALTER TABLE message DROP name');
        $this->connection->executeQuery('ALTER TABLE parameter DROP name');
        $this->connection->executeQuery('ALTER TABLE parameter DROP parameter_group_id');

        $this->connection->executeQuery('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FD823E37A FOREIGN KEY (section_id) REFERENCES section (id)');
        $this->connection->executeQuery('ALTER TABLE parameter CHANGE section_id section_id VARCHAR(100) NOT NULL');
        $this->connection->executeQuery('ALTER TABLE parameter ADD CONSTRAINT FK_2A979110D823E37A FOREIGN KEY (section_id) REFERENCES section (id)');
        $this->connection->executeQuery('CREATE INDEX IDX_2A979110D823E37A ON parameter (section_id)');
        $this->connection->executeQuery('ALTER TABLE bike_ride_type_message ADD CONSTRAINT FK_68B5B8DE537A1329 FOREIGN KEY (message_id) REFERENCES message (id) ON DELETE CASCADE');
    }


    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FD823E37A');
        $this->addSql('ALTER TABLE parameter DROP FOREIGN KEY FK_2A979110D823E37A');
        $this->addSql('ALTER TABLE bike_ride_type_message DROP FOREIGN KEY FK_68B5B8DE537A1329');

        $this->addSql('CREATE TABLE parameter_group (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, label VARCHAR(255) NOT NULL, role VARCHAR(25) NOT NULL, UNIQUE INDEX UNIQ_BC65C5865E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE bike_ride_type_message CHANGE message_id message_id INT NOT NULL');
        $this->addSql('ALTER TABLE message CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE section_id section_id INT DEFAULT NULL, ADD name VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE parameter CHANGE id id INT AUTO_INCREMENT NOT NULL, ADD parameter_group_id INT DEFAULT NULL, DROP section_id');

        $this->addSql('DROP TABLE section');
    }

    public function postDown(Schema $schema): void
    {
        $this->connection->executeQuery('INSERT INTO parameter_group (name, label, role) SELECT id, label, role FROM section');

        $this->connection->executeQuery('UPDATE parameter p INNER JOIN parameter_group pg ON pg.name = p.id SET p.parameter_group_id = pg.id');
        
        $this->connection->executeQuery('ALTER TABLE parameter CHANGE parameter_group_id parameter_group_id INT NOT NULL');
        $this->connection->executeQuery('ALTER TABLE parameter ADD CONSTRAINT FK_2A979110132604DB FOREIGN KEY (parameter_group_id) REFERENCES parameter_group (id)');
        $this->connection->executeQuery('CREATE INDEX IDX_2A979110132604DB ON parameter (parameter_group_id)');
        
        $this->connection->executeQuery('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FD823E37A FOREIGN KEY (section_id) REFERENCES parameter_group (id)');
        $this->connection->executeQuery('ALTER TABLE bike_ride_type_message ADD CONSTRAINT FK_68B5B8DE537A1329 FOREIGN KEY (message_id) REFERENCES message (id)');
    }
}
