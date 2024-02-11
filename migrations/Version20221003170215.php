<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221003170215 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $parameter = [
            'content' => '<p>Vous venez de cr&eacute;er un compte sur notre site</p><p><strong>Voici votre indentifiant pour vous connecter : </strong>{{ licenceNumber }}<br />Vous seul connaissez le mot de passe</p><p><strong>Pour valider votre inscription, vous devez obligatoirement</strong></p> <ul><li>Remettre le dossier d&#39;inscription au club, signer, que vous pouver t&eacute;l&eacute;charger,&nbsp;&nbsp;avec le lien&nbsp;<a href="https://vttevasionludres.fr/mon-compte">https://vttevasionludres.fr Mes infos</a></li><li>vous inscrire sur le site &agrave; une sortie ou &agrave; une s&eacute;ance de l&#39;&eacute;cole VTT en cliquant sur ce lien.&nbsp;<a href="https://vttevasionludres.fr/programme">https://vttevasionludres.fr onglet &quot;Programme&quot;</a></li></ul>',
            'name' => 'EMAIL_REGISTRATION',
        ];

        $this->addSql('UPDATE `parameter` SET `value`= :content WHERE `name` = :name', $parameter);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
