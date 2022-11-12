<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221111153152 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE disease_kind ADD licence_category INT DEFAULT NULL');
        $this->addSql('UPDATE `registration_step` SET `title`=\'Fiche sanitaire\', `category`= null WHERE `id` = 6');
        $registrationsSteps = [
            ['id' => 3, 'orderBy' => 0],
            ['id' => 12, 'orderBy' => 1],
            ['id' => 6, 'orderBy' => 2],
        ];
        foreach($registrationsSteps as $registrationsStep) {
            $this->addSql('UPDATE `registration_step` SET `order_by`=:orderBy WHERE `id` = :id', $registrationsStep);
        }
        
        $this->addSql('UPDATE `disease_kind` SET `licence_category`= 1 WHERE `id` = 1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE disease_kind DROP licence_category');
    }
}
