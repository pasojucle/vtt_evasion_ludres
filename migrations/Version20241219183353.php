<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241219183353 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE documentation ADD link VARCHAR(255) DEFAULT NULL');
        foreach ($this->getParametersGroup() as $parameterGroup) {
            $this->addSql('INSERT INTO `parameter_group`(`name`, `label`, `role`) VALUES (:name, :label, :role)', $parameterGroup);
        }
        foreach ($this->getMessages() as $message) {
            $this->addSql('INSERT INTO `message`(`name`, `label`, `content`, `section_id`) VALUES (:name, :label, :content, (SELECT `id` FROM `parameter_group` WHERE name LIKE :parameterGroupName))', $message);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE documentation DROP link');
        foreach ($this->getMessages() as $message) {
            $this->addSql('DELETE FROM `message` WHERE `name` = :name', $message);
        }
        foreach ($this->getParametersGroup() as $parameterGroup) {
            $this->addSql('DELETE FROM `parameter_group` WHERE `name` = :name', $parameterGroup);
        }
    }

    private function getParametersGroup(): array
    {
        return [
            [
                'name' => 'DOCUMENTATION',
                'label' => 'Documentation',
                'role' => 'NONE',
            ],
        ];
    }

    private function getMessages(): array
    {
        return [
            [
                'name' => 'DOCUMENTATION_LINK_WARNING_MESSAGE',
                'label' => 'Message de non-responsablité du contenu d\'un site externe',
                'content' => '<p>Vous allez être redirigé vers un site web externe détenu et exploité par un tiers indépendant. Nous ne pouvons pas être tenu responsable de leur contenu. Tout accès vers ou depuis ce site externe se fera à vos propres risques.</p>',
                'parameterGroupName' => 'DOCUMENTATION'
            ],
        ];
    }
}
