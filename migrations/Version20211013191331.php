<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211013191331 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }
    
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO `parameter` (`name`, `label`, `type`, `value`) VALUES
        ('EMAIL_FORM_CONTACT', 'Message de réponse du formulaire de contact', 1, '<p>Nous avons bien pris en compte votre demande, et nous allons y répondre au plus vite.</p>'),
        ('EMAIL_REGISTRATION', 'Message lors de la création de compte', 1, '<p>Vous venez de créer un compte sur notre site</p>\r\n    <p>\r\n        <b>Voici votre indentifiant pour vous connecter : </b>{{ licenceNumber }}<br>\r\n        Vous seul connaissez le mot de passe\r\n    </p>\r\n    <p>\r\n        Pour valider votre inscription, vous devez vous inscrire sur le site à une sortie ou à une séance de l\'école VTT en cliquant sur ce lien.<br>\r\n        <a href=\"https://vttevasionludres.fr/programme\">https://vttevasionludres.fr onglet \"Programme\"</a> \r\n    </p>'),
        ('EMAIL_END_TESTING', 'Fin de période de test', 1, ' <p>Vous venez de vous inscrire à votre dernière séance d\'essai au club.</p>\r\n    <p>Si vous souhaitez continuer l\'aventure, inscrivez-vous dès maintenant pour la saison entière en cliquant sur <a href=\"http://vttevasionludres.fr/mon-compte/inscription/1\">ce lien</a></p>'),
        ('EMAIL_LICENCE_VALIDATE', 'Envoie mail à la validation de licence', 1, '<p>Veuillez trouver votre numéro de licence, qui sera désormais  votre identifiant pour vous connecter à votre compte.</p>\r\n<p><b>{{ licenceNumber }} </b></p>\r\n<p>Le mot de passe reste inchangé</p>')");
        $this->addSql("UPDATE `user` SET `roles`='[\"ROLE_REGISTER\"]' WHERE `licence_number` = '771815'");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
