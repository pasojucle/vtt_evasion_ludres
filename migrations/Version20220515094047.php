<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220515094047 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("UPDATE `registration_step` SET `form`= 11 WHERE `title` = 'Informations du parent ou tuteur de l\'enfant'");
        $this->addSql("UPDATE `registration_step` SET `form`= 10 WHERE `title` = 'Informations de l\'adhérent'");
        $this->addSql("UPDATE registration_step SET `content` = REPLACE(`content`, 'nom_prenom_parent', 'prenom_nom_parent')");
        $this->addSql("UPDATE registration_step SET `content` = REPLACE(`content`, 'nom_prenom_enfant', 'prenom_nom_enfant')");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
