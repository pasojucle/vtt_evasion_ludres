<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211111135827 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO `parameter` (`name`, `label`, `type`, `value`) VALUES
        ('REGISTRATION_CERTIFICATE_ADULT', 'Attestation d\'inscription pour CE adulte', 1, '<p>Je soussigné : Sylvain KOHLER</p>\r\n<p>Président de l\’association VTT Évasion Ludres</p>\r\n<br>\r\n<br>\r\n<p>Certifie que : {{ nom_prenom }}</p>\r\n<p>Demeurant : {{ adresse }}</p>\r\n<p>Est inscrit(e) pour la saison sportive : {{ saison }}</p>\r\n<p>sous le numéro de licence {{ numero_licence }} de la fédération française de cyclotourisme</p>\r\n<p>Et a versé une cotisation globale de : {{ montant }}</p>\r\n<br>\r\n<p>Cette attestation a été établie pour servir et valoir ce que de droit</p>\r\n<br>\r\n<br>\r\n<p>Fait à Ludres le {{ date }}</p>\r\n<p>Président</p>\r\n<p>Sylvain KOHLER</p>\r\n'),
        ('REGISTRATION_CERTIFICATE_SCHOOL', 'Attestation d\'inscription pour CE école VTT', 1, '<p>Je soussigné : Sylvain KOHLER</p>\r\n<p>Président de l\’association VTT Évasion Ludres</p>\r\n<br>\r\n<br>\r\n<p>Certifie que : {{ nom_prenom_parent }}</p>\r\n<p>Demeurant : {{ adresse_parent }}</p>\r\n<p>A inscrit son enfant {{ nom_prenom_enfant }} pour la saison sportive : {{ saison }}</p>\r\n<p>sous le numéro de licence {{ numero_licence }}  de la fédération française de cyclotourisme</p>\r\n<p>Et a versé une cotisation globale de : {{ montant }}</p>\r\n<br>\r\n<p>Cette attestation a été établie pour servir et valoir ce que de droit</p>\r\n<br>\r\n<br>\r\n<p>Fait à Ludres le {{ date }}</p>\r\n<p>Président</p>\r\n<p>Sylvain KOHLER</p>\r\n')");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
