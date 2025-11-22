<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251122142839 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $message = [
            'name' => 'EMAIL_END_TESTING',
            'content' => '<p>Vous venez de participer &agrave; votre 3e et derni&egrave;re s&eacute;ance d&#39;essai gratuite au club.</p><p>Si vous souhaitez continuer l&#39;aventure, inscrivez-vous d&egrave;s maintenant pour la saison enti&egrave;re en cliquant sur <a href="http://vttevasionludres.fr/mon-compte/inscription/1">ce lien</a></p>'
        ];
        $this->addSql('UPDATE `message` SET `content`=:content WHERE `name`=:name', $message);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
