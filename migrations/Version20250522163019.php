<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250522163019 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE identity ADD birth_country VARCHAR(50) DEFAULT NULL');;
    }

    public function postUp(Schema $schema): void
    {
        $result = $this->connection->executeQuery('SELECT * FROM `identity` WHERE birth_place IS NOT null AND birth_commune_id IS null');
        
        $identities = $result->fetchAllAssociative();
        foreach($identities as $identity) {
            if (1 === preg_match('#^([A-Za-z\s]+)\(([A-Za-z\s]+)\)$#', $identity['birth_place'], $matches)) {
                $statement = $this->connection->prepare('UPDATE `identity` set `birth_place`=:birthPlace, `birth_country`=:birthCountry WHERE id=:id');
                $statement->bindValue('id', $identity['id']);
                $statement->bindValue('birthPlace', $matches[1]);
                $statement->bindValue('birthCountry', $matches[2]);
                $statement->executeQuery();
            };
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE identity DROP birth_country');
    }
}
