<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200822145139 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE parameter ADD options LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', ADD order_by INT DEFAULT NULL, CHANGE value value VARCHAR(50) DEFAULT NULL');
        $parameters =[
            [
                'id' => 1,
                'name' => 'THEME_CSS',
                'label' => 'Thème',
                'value' => 'dark-theme',
                'type' => 'choice',
                'options' => ['dark-theme', 'ligth-theme'],
                'orderBy' => 3,
            ],
            [
                'id' => 2,
                'name' => 'ENCRYPTION',
                'label' => 'Encrypter les données',
                'value' => '0',
                'type' => 'bool',
                'options' => null,
                'orderBy' => 4,
            ],
            [
                'id' => null,
                'name' => 'PROJECT_NAME',
                'label' => 'Non du site',
                'value' => 'test',
                'type' => 'text',
                'options' => null,
                'orderBy' => 1,
            ],
            [
                'id' => null,
                'name' => 'FAVICON',
                'label' => 'Favicon',
                'value' => null,
                'type' => 'image',
                'options' => null,
                'orderBy' => 2,
            ],
        ];
        foreach ($parameters as $parameter) {
            if (null !== $parameter['options']) {
                $parameter['options'] = json_encode($parameter['options']);
            }
            if (null == $parameter['id']) {
                $this->addSql('INSERT INTO  parameter (name, label, value, type, options, order_by) VALUES (:name, :label, :value, :type, :options, :orderBy)', $parameter);
            } else {
                $this->addSql('UPDATE parameter SET options = :options, order_by = :orderBy WHERE id = :id', $parameter);
            }
        }
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE parameter DROP options, DROP order_by, CHANGE value value VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
