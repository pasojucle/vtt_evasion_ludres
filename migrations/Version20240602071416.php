<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Parameter;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240602071416 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE log (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, view_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', route VARCHAR(255) DEFAULT NULL, entity VARCHAR(50) DEFAULT NULL, entity_id INT DEFAULT NULL, INDEX IDX_8F3F68C5A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE respondent DROP survey_changed');
        $this->addSql('ALTER TABLE history ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('UPDATE `history` SET `created_at`= CONCAT(season, \'-01-01\') WHERE `season` IS NOT NULL');
        $this->addSql('ALTER TABLE history DROP season');

        $parameter = [
            'name' => 'LOG_DURATION',
            'label' => 'Durrée de concervation des logs (en jours)',
            'type' => Parameter::TYPE_INTEGER,
            'value' => '90',
            'parameterGroupName' => 'MAINTENANCE'
        ];

        $this->addSql('INSERT INTO `parameter`(`name`, `label`, `type`, `value`, `parameter_group_id`) VALUES (:name, :label, :type, :value, (SELECT `id` FROM `parameter_group` WHERE name LIKE :parameterGroupName))', $parameter);
    
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5A76ED395');
        $this->addSql('DROP TABLE log');
        $this->addSql('ALTER TABLE respondent ADD survey_changed TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE history ADD season INT DEFAULT NULL, DROP created_at');
    }
}
