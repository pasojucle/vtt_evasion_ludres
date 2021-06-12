<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210612121017 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE membership_fee ADD additional_family_member TINYINT(1) NOT NULL, ADD new_member TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE membership_fee_amount DROP additional_family_member, DROP new_member');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE membership_fee DROP additional_family_member, DROP new_member');
        $this->addSql('ALTER TABLE membership_fee_amount ADD additional_family_member TINYINT(1) NOT NULL, ADD new_member TINYINT(1) NOT NULL');
    }
}
