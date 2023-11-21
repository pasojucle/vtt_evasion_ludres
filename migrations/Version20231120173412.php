<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231120173412 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        // $this->addSql('ALTER TABLE content ADD parameters JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('UPDATE `parameter_group` SET `name`=\'CONTENT\',`role`=\'NONE\' WHERE `name`=\'MESSAGES\'');

        $contents = [
            ['route' => 'contact', 'parameters' => json_encode(['EMAIL_FORM_CONTACT'])],
            ['route' => 'user_change_infos', 'parameters' => json_encode(['EMAIL_CHANGE_USER_INFOS'])],
        ];
        foreach ($contents as $content) {
            $this->addSql('UPDATE `content` SET `parameters`=:parameters WHERE `route`=:route', $content);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE content DROP parameters');
    }
}
