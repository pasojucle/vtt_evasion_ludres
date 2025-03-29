<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250329061644 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $message = [
            'name' => 'ORDER_ACKNOWLEDGEMENT_MESSAGE',
            'label' => 'Message à afficher sur une commande validée par le club',
            'content' => '<p>VOTRE COMMANDE EST VALIDÉE.</p><p>ELLE A PEUT ÊTRE ÉTÉ MODIFIÉE EN FONCTION DES STOCKS.</p><p><strong>Document &agrave; imprimer<br /> et &agrave; envoyer accompagn&eacute; de votre r&eacute;glement<br />A l&#39;adresse suivante :<br />VTT Evasion Ludres<br />184 rue Saint Exup&eacute;ry - 54710 Ludres</strong></p>',
        ];
        $this->addSql('UPDATE message set content=:content, label=:label WHERE name = :name', $message);

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
