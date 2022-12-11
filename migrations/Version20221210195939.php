<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221210195939 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        $parameter = [
            'content' => '<p>Vous avez un dossier d\'inscription non finalisé. Souhaitez-vous terminer et valider votre inscription ?</P>',
            'name' => 'MODAL_WINDOW_REGISTRATION_IN_PROGRESS',
            'label' => 'Message du pop\'up pour l\'inscription en cours',
            'type' => 1,
            'parameterGroup' => 2,
        ];

        $this->addSql('INSERT INTO `parameter`(`name`, `label`, `type`, `value`, `parameter_group_id`) VALUES (:name, :label, :type, :content, :parameterGroup)', $parameter);

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
