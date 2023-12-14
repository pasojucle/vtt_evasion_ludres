<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Parameter;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221222171654 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $content = [
            'route' => 'user_change_infos', 
            'content' => '<h1>Modifications d&#39;infos personnelles</h1><p>Pour toutes modifications d&#39;informations personnelles, veuillez nous transmettre votre demande &agrave; l&#39;aide du formulaires ci-dessous.&nbsp;</p>',
            'isActive' => 1,
            'isFlash' => 0,
            'backgroundOnly' => 0,
            'orderBy' => 18,
        ];

        $this->addSql('INSERT INTO `content`(`route`, `content`, `is_active`, `is_flash`, `background_only`, `order_by`) VALUES (:route, :content, :isActive, :isFlash, :backgroundOnly, :orderBy)', $content);
        $parameter = [
            'name' => 'EMAIL_CHANGE_USER_INFOS',
            'label' => 'Message de confirmation de demande de modification d\'infos personnelles',
            'content' =>' <p>Nous avons bien pris en compte votre demande et allons faire les modifications dans les meilleurs d&eacute;lais.</p>',
            'type' => Parameter::TYPE_HTML,
            'parameterGroup' => 2,
        ];
        
        $this->addSql('INSERT INTO `parameter`(`name`, `label`, `type`, `value`, `parameter_group_id`) VALUES (:name, :label, :type, :content, :parameterGroup)', $parameter);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE FROM `content`WHERE `route` =\'user_change_infos\'');
        $this->addSql('DELETE FROM `parameter`WHERE `name` =\'EMAIL_CHANGE_USER_INFOS\'');
    }
}
