<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220917074015 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD protected TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('UPDATE `user` SET  `protected` = 1 WHERE `licence_number` = \'webmaster\'');

        $this->addSql("INSERT INTO `parameter`(`name`, `label`, `type`, `value`, `parameter_group_id`) VALUES ('ORDER_ACKNOWLEDGEMENT_MESSAGE', 'Message à afficher en pied de la confirmation de commande',1,'<b>Document à imprimer <br>et à envoyer accompagné de votre réglement <br>pour le 31 octobre au plus tard<br>A l\'adresse suivante : <br>VTT Evasion Ludres<br>184 rue Saint Exupéry - 54710 Ludres</b>', 2)");

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP protected');
    }
}
