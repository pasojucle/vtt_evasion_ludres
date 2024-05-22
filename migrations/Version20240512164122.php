<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240512164122 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE history (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, entity VARCHAR(100) NOT NULL, entity_id INT NOT NULL, value JSON NOT NULL COMMENT \'(DC2Type:json)\', season INT DEFAULT NULL, INDEX IDX_27BA704BA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE history ADD CONSTRAINT FK_27BA704BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE registration_change DROP FOREIGN KEY FK_EE093ECAA76ED395');
        $this->addSql('INSERT INTO `history`(`id`, `user_id`, `entity`, `entity_id`, `value`, `season`) SELECT `id`, `user_id`, `entity`, `entity_id`, `value`, `season` FROM `registration_change`');
        $this->addSql('ALTER TABLE respondent ADD survey_changed TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('DROP TABLE registration_change');

        $message = [
            'name' => 'SURVEY_CHANGED_MESSAGE',
            'label' => 'Message pour notifier des modifications dans un sondage',
            'content' => '<p>Le sondage {{ sondage }} a été modifié</p>',
            'levelType' => null,
            'protected' => 1,
            'sectionName' => 'SURVEY',
        ];
        $this->addSql('INSERT INTO `message`(`section_id`, `name`, `label`, `content`, `level_type`, `protected`) VALUES ( (SELECT `id` FROM `parameter_group` WHERE name LIKE :sectionName), :name, :label, :content, :levelType, :protected)', $message);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE registration_change (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, entity VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, entity_id INT NOT NULL, value JSON NOT NULL COMMENT \'(DC2Type:json)\', season INT NOT NULL, INDEX IDX_EE093ECAA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE registration_change ADD CONSTRAINT FK_EE093ECAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE history DROP FOREIGN KEY FK_27BA704BA76ED395');
        $this->addSql('INSERT INTO `registration_change`(`id`, `user_id`, `entity`, `entity_id`, `value`, `season`) SELECT `id`, `user_id`, `entity`, `entity_id`, `value`, `season` FROM `history` WHERE `user_id` IS NOT NULL');
        $this->addSql('ALTER TABLE respondent DROP survey_changed');
        $this->addSql('DROP TABLE history');
        $this->addSql('DELETE FROM `message` WHERE `name`=\'SURVEY_CHANGED_MESSAGE\'');

    }
}
