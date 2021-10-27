<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211027193053 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO `parameter` (`name`, `label`, `type`, `value`) VALUES
        ('EMAIL_REGISTRATION_ERROR', 'Envoie mail Suite à un problème d\'inscription', 1, '<p>Faisant suite aux probl&egrave;mes que vous avez rencontr&eacute; lors de votre inscription, nous vous invitons &agrave; suivre <a href=\"http://vttevasionludres.fr/mon-compte/inscription/1\">ce lien</a>&nbsp;pour acc&eacute;der &agrave; votre dossier.</p>\r\n\r\n<p>Apr&egrave;s avoir renseign&eacute; les informations du parent ou tuteur, vous pourrez t&eacute;l&eacute;charger le dossier &agrave; nous remettre sign&eacute;.</p>\r\n\r\n<p><strong>Voici votre identifiant pour vous connecter : </strong>{{ licenceNumber }}</p><p>Vous seul connaissez le mot de passe</p>');");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
