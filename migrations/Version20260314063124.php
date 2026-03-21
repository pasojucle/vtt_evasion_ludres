<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260314063124 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE public_registration_rate (id INT AUTO_INCREMENT NOT NULL, practice VARCHAR(255) DEFAULT \'vtt\' NOT NULL, max_age INT NOT NULL, amount INT NOT NULL, ffvelo TINYINT(1) DEFAULT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cluster ADD practice VARCHAR(255) DEFAULT \'vtt\' NOT NULL');
        $this->addSql('ALTER TABLE licence ADD ffvelo TINYINT(1) DEFAULT 0 NOT NULL, ADD club_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE session ADD bike_type VARCHAR(255) DEFAULT \'none\' NOT NULL');
    }

    public function postUp(Schema $schema): void
    {
        foreach($this->getPublicRegistrationRate() as $line) {
            $this->connection->executeQuery('INSERT INTO public_registration_rate (label, practice, max_age, FFVelo, amount) VALUES (:label, :practice, :maxAge, :FFVelo, :amount)', $line);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE public_registration_rate');
        $this->addSql('ALTER TABLE cluster DROP practice');
        $this->addSql('ALTER TABLE licence DROP ffvelo, DROP club_name');
        $this->addSql('ALTER TABLE session DROP bike_type');
    }

    private function getPublicRegistrationRate(): array
    {
        return [
            [
                'label' => 'VTT ou Gravel -18 ans',
                'practice' => 'vtt',
                'maxAge' => 18,
                'FFVelo' => 0,
                'amount' => 500,
            ],
            [
                'label' => 'VTT ou Gravel',
                'practice' => 'vtt',
                'maxAge' => 99,
                'FFVelo' => 0,
                'amount' => 800,
            ],
            [
                'label' => 'VTT ou Gravel club FFVélo -18 ans',
                'practice' => 'vtt',
                'maxAge' => 18,
                'FFVelo' => 1,
                'amount' => 0,
            ],
            [
                'label' => 'VTT ou Gravel club FFVélo',
                'practice' => 'vtt',
                'maxAge' => 99,
                'FFVelo' => 1,
                'amount' => 500,
            ],
            [
                'label' => 'Marche',
                'practice' => 'walking',
                'maxAge' => 99,
                'FFVelo' => null,
                'amount' => 500,
            ],
        ];
    }
}
