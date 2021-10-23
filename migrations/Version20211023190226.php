<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211023190226 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product ADD discount_price DOUBLE PRECISION DEFAULT NULL, ADD discount_title VARCHAR(50) DEFAULT NULL, ADD category INT DEFAULT NULL');
        $this->addSql('UPDATE `product` SET `discount_price`=20,`discount_title`=\'Prix spécial école VTT\',`category`=1 WHERE `id` = 1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product DROP discount_price, DROP discount_title, DROP category');
    }
}
