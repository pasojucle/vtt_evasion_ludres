<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210909160758 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $html = '<p style=\"text-align:center\">Voici le r&eacute;capitulatif des informations saisies.</p>\r\n\r\n<p style=\"text-align:center\">Nous vous invitons &agrave; les relire, et &agrave; les modifier si besoin.</p>\r\n\r\n<p style=\"text-align:center\">Apr&egrave;s validation, aucune modification sera possible.</p>';
        $this->addSql('INSERT INTO `registration_step` (`title`, `filename`, `form`, `order_by`, `content`, `category`, `to_pdf`, `testing_render`) VALUES
        (\'Validation\', NULL, 9, 9,\''.$html.'\', NULL, 0, 2);');

        $this->addSql("UPDATE `registration_step` SET `title`= 'Téléchargement', `order_by` = 10 WHERE `title` LIKE 'Validation et téléchargement'");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
