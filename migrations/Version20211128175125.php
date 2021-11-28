<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211128175125 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE parameter DROP FOREIGN KEY FK_2A979110132604DB');
        $this->addSql('DROP INDEX IDX_2A979110132604DB ON parameter');
        $this->addSql('ALTER TABLE parameter CHANGE parameter_group_id parameter_group_id INT NOT NULL');
        $this->addSql('ALTER TABLE parameter ADD CONSTRAINT FK_2A979110132604DB FOREIGN KEY (parameter_group_id) REFERENCES parameter_group (id)');
        $this->addSql('CREATE INDEX IDX_2A979110132604DB ON parameter (parameter_group_id)');
        $this->addSql("INSERT INTO `parameter` (`name`, `label`, `type`, `value`, `parameter_group_id`) VALUES
        ('EVENT_HOLIDAYS_CONTENT', 'ÉcoleVTT: Vacances scolaires', 1, 'Il n\'y aura pas de séances d\'école VTT les samedis 30 octobre et 06 novembre. Reprise le samedi 13 novembre.\r\n\r\nBonnes vacances à toutes et à tous', 1)");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
