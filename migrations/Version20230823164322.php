<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230823164322 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, deleted TINYINT(1) DEFAULT 0 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE second_hand (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, category_id INT NOT NULL, name VARCHAR(50) NOT NULL, content LONGTEXT NOT NULL, filename VARCHAR(255) NOT NULL, price INT NOT NULL, valid TINYINT(1) DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted TINYINT(1) NOT NULL, INDEX IDX_A325FA21A76ED395 (user_id), INDEX IDX_A325FA2112469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE second_hand ADD CONSTRAINT FK_A325FA21A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE second_hand ADD CONSTRAINT FK_A325FA2112469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $content = [
            'route' => 'second_hand', 
            'content' => '<p>Déposer ici votre annonce pour proposer à la vente du matériel d\'occasion à destination des membres du club. Elle sera en ligne seulement après validation par le modérateur.</p>',
            'isActive' => 1,
            'isFlash' => 0,
            'backgroundOnly' => 0,
            'orderBy' => 19,
        ];

        $this->addSql('INSERT INTO `content`(`route`, `content`, `is_active`, `is_flash`, `background_only`, `order_by`) VALUES (:route, :content, :isActive, :isFlash, :backgroundOnly, :orderBy)', $content);
        
        $categories = ['Vélo', 'Composants', 'Accessoires', 'Vêtements'];
        foreach($categories as $category) {
            $value = ['name' => $category];
            $this->addSql('INSERT INTO `category`(`name`) VALUES(:name)', $value);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE second_hand DROP FOREIGN KEY FK_A325FA21A76ED395');
        $this->addSql('ALTER TABLE second_hand DROP FOREIGN KEY FK_A325FA2112469DE2');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE second_hand');
    }
}
