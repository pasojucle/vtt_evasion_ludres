<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221217064145 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE board_role (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, board TINYINT(1) DEFAULT 0 NOT NULL, order_by INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $boardRoles = [
            ['name' => 'Président', 'board' => 1, 'orderBy' => 0],
            ['name' => 'Vice-Président', 'board' => 1, 'orderBy' => 1],
            ['name' => 'Secrétaire', 'board' => 1, 'orderBy' => 2],
            ['name' => 'Secrétaire adjoint', 'board' => 1, 'orderBy' => 3],
            ['name' => 'Trésorier', 'board' => 1, 'orderBy' => 4],
            ['name' => 'Trésorier adjoint', 'board' => 1, 'orderBy' => 5],
            ['name' => 'Membre du comité', 'board' => 0, 'orderBy' => 6],
        ];

        foreach($boardRoles as $boardRole) {
            $this->addSql('INSERT INTO `board_role`(`name`, `board`, `order_by`) VALUES (:name, :board, :orderBy)', $boardRole);
        }

        $this->addSql('ALTER TABLE user ADD board_role_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6499E1D0AA3 FOREIGN KEY (board_role_id) REFERENCES board_role (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6499E1D0AA3 ON user (board_role_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6499E1D0AA3');
        $this->addSql('DROP INDEX IDX_8D93D6499E1D0AA3 ON user');
        $this->addSql('ALTER TABLE user DROP board_role_id');
        $this->addSql('DROP TABLE board_role');
    }
}
