<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260606050950 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT INTO `message`(`section_id`, `name`, `label`, `content`, `level_type`, `protected`) VALUES ( (SELECT `id` FROM `parameter_group` WHERE name LIKE :sectionName), :name, :label, :content, :levelType, :protected)', $this->getMessage());
    
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE FROM `message` WHERE name=:name', $this->getMessage());
    }

    private function getMessage(): array
    {
        return [
            'name' => 'REGISTRATION_REJECT_MESSAGE',
            'label' => 'Message pour notifier le rejet d\'un dossier d\'inscription incomplete ',
            'content' => '<p>Le dossier d\'inscription au club est incomplet ou non conforme. Merci de le transmettre à nouveau, signé, en tenant compte des modifications suivantes :</p>',
            'levelType' => null,
            'protected' => 1,
            'sectionName' => 'REGISTRATION',
        ];
    }
}
