<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241211171543 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE second_hand_image (id INT AUTO_INCREMENT NOT NULL, second_hand_id INT NOT NULL, filename VARCHAR(255) NOT NULL, INDEX IDX_3119EB94FA993434 (second_hand_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE second_hand_image ADD CONSTRAINT FK_3119EB94FA993434 FOREIGN KEY (second_hand_id) REFERENCES second_hand (id)');
        $this->addSql('INSERT INTO `second_hand_image` (`second_hand_id`, `filename`) SELECT `id`, `filename` FROM `second_hand`');
        $this->addSql('ALTER TABLE second_hand DROP filename');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE second_hand ADD filename VARCHAR(255) NOT NULL');
        $this->addSql('UPDATE `second_hand` INNER JOIN second_hand_image ON second_hand.id=second_hand_image.second_hand_id SET `second_hand`.`filename`= `second_hand_image`.`filename`');
        $this->addSql('ALTER TABLE second_hand_image DROP FOREIGN KEY FK_3119EB94FA993434');
        $this->addSql('DROP TABLE second_hand_image');
    }
}
