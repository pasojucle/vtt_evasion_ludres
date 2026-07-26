<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260726151249 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE emergency_contact (id INT AUTO_INCREMENT NOT NULL, member_id INT DEFAULT NULL, phone VARCHAR(14) NOT NULL, kinship VARCHAR(100) DEFAULT NULL, UNIQUE INDEX UNIQ_FE1C61907597D3FE (member_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE emergency_contact ADD CONSTRAINT FK_FE1C61907597D3FE FOREIGN KEY (member_id) REFERENCES user (id)');
    }

    public function postUp(Schema $schema): void
    {
        $emergecyContacts = $this->connection
            ->executeQuery('SELECT user_id, emergency_phone, emergency_contact FROM identity WHERE emergency_phone IS NOT NULL')
            ->fetchAllAssociative();
        foreach($emergecyContacts as $emergecyContact) {
            $this->connection->executeQuery('INSERT INTO emergency_contact (member_id, phone, kinship) VALUES (:member, :phone, :kinship)',
            ['member' => $emergecyContact['user_id'], 'phone' => $emergecyContact['emergency_phone'], 'kinship' => $emergecyContact['emergency_contact']]);
        }

        $this->connection->executeQuery('ALTER TABLE identity DROP emergency_phone, DROP emergency_contact');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE emergency_contact DROP FOREIGN KEY FK_FE1C61907597D3FE');
        $this->addSql('ALTER TABLE identity ADD emergency_phone VARCHAR(14) DEFAULT NULL, ADD emergency_contact VARCHAR(255) DEFAULT NULL');
    }

    public function postDown(Schema $schema): void
    {
        $this->connection->executeQuery('UPDATE identity i JOIN emergency_contact ec ON i.user_id = ec.member_id SET i.emergency_phone = ec.phone, i.emergency_contact = ec.kinship');

        $this->connection->executeQuery('DROP TABLE emergency_contact');
    }
}
