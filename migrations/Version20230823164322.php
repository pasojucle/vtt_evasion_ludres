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
        $contents = [
            [
                'route' => 'second_hand', 
                'content' => '<p>Déposer ici votre annonce pour proposer à la vente du matériel d\'occasion à destination des membres du club. Elle sera en ligne seulement après validation par le modérateur.</p><p>Les personnes intéressées vous contacterons via un formulaire depuis le site.</p>',
                'isActive' => 1,
                'isFlash' => 0,
                'backgroundOnly' => 0,
                'orderBy' => 19,
            ], 
            [
                'route' => 'second_hand_contact', 
                'content' => '<h1>Annonce d\'occasion</h1><p>Pour contacter le vendeur, veuillez lui transmettre votre demande &agrave; l&#39;aide du formulaires ci-dessous.&nbsp;</p>',
                'isActive' => 1,
                'isFlash' => 0,
                'backgroundOnly' => 0,
                'orderBy' => 20,
            ],
        ];
        foreach($contents as $content) {
            $this->addSql('INSERT INTO `content`(`route`, `content`, `is_active`, `is_flash`, `background_only`, `order_by`) VALUES (:route, :content, :isActive, :isFlash, :backgroundOnly, :orderBy)', $content);
        }
        
        $categories = ['Vélo', 'Composants', 'Accessoires', 'Vêtements'];
        foreach($categories as $category) {
            $value = ['name' => $category];
            $this->addSql('INSERT INTO `category`(`name`) VALUES(:name)', $value);
        }

        $parameter = [
            'name' => 'SECOND_HAND_CONTACT',
            'label' => 'Message de prise de contact à une annonce d\'occasion',
            'type' => 1,
            'value' => '<p>L\'article de votre annonce {{ nom_annonce }} m’intéresse</p><p>Pouvez-vous me contacter par téléphone au {{ telephone }} ou par mail à l\'adresse {{ email }}</p><p>{{ prenom_nom }}</p>',
            'group' => 2,
        ];
        $this->addSql('INSERT INTO `parameter` (`name`, `label`, `type`, `value`, `parameter_group_id`) VALUES (:name, :label, :type, :value, :group)', $parameter);
        
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
