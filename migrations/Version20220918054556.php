<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220918054556 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        $content = '<h2>Proc&eacute;dure pour cr&eacute;er un compte et s&rsquo;inscrire au VTT Evasion LUDRES</h2><h3>Utiliser un<span style="color:#c0392b"> <strong>ordinateur</strong></span> et ne pas le faire sur un smartphone ou tablette.</h3><p><img alt="" src="/images/tuto/screen_1.jpg" style="width:100%" /></p><h3>Inscription /&nbsp;S&rsquo;inscrire</h3><p><img alt="" src="/images/tuto/screen_2.jpg" style="width:100%" /></p><h3>Aller en bas de page et&nbsp;s&eacute;lectionner 3 s&eacute;ances d&rsquo;essai</h3><p><img alt="" src="/images/tuto/screen_3.jpg" style="width:100%" /></p><h3>Renseigner l&rsquo;ensemble des &eacute;l&eacute;ments et&nbsp;<span style="color:#c0392b"><strong>bien noter le mot de passe que&nbsp;vous aurez choisi&nbsp;</strong></span>entre 6 &agrave; 10&nbsp;caract&egrave;res.<br />
        Ensuite faire enregistrer.</h3><p><img alt="" src="/images/tuto/screen_4.png" style="width:100%" /></p><p><img alt="" src="/images/tuto/screen_5.jpg" style="width:100%" /></p><p><img alt="" src="/images/tuto/screen_6.jpg" style="width:100%" /></p><p><img alt="" src="/images/tuto/screen_7.jpg" style="width:100%" /></p><h3><strong>IMPORTANT :&nbsp;</strong>T&eacute;l&eacute;charger le fichier puis l&rsquo;imprimer et signer les diverses pages.<br />Suivant le cas s&rsquo;il s&rsquo;agit d&rsquo;un adulte ou d&rsquo;un mineur, les documents ne sont pas les m&ecirc;mes.</h3><h3>Avant de poursuivre penser &agrave; se d&eacute;connecter du site pour r&eacute;initialiser votre session.</h3><p><img alt="" src="/images/tuto/screen_8.jpg" style="width:100%" /></p><p><img alt="" src="/images/tuto/screen_9.jpg" style="width:100%" /></p><h3>Noter votre identifiant qui vous a &eacute;t&eacute; communiqu&eacute; par mail comme ci-dessous</h3><p><img alt="" src="/images/tuto/screen_10.jpg" style="width:100%" /></p><h3>Retourner sur la page d&rsquo;accueil et s&rsquo;inscrire &agrave; la s&eacute;ance du samedi</h3><p><img alt="" src="/images/tuto/screen_11.jpg" style="width:100%" /></p> <p><img alt="" src="/images/tuto/screen_12.jpg" style="width:100%" /></p><p><img alt="" src="/images/tuto/screen_13.jpg" style="width:100%" /></p><p><img alt="" src="/images/tuto/screen_14.jpg" style="width:100%" /></p><h3>Vous pouvez vous d&eacute;sinscrire jusqu&rsquo;au jeudi soir minuit<br />Pour se d&eacute;sinscrire se connecter regarder son programme perso et cliquer sur la croix pour modifier</h3>';
        $contents = [
            ['route' => 'registration_tuto', 'content' => $content, 'isActive' => 1, 'isFlash' => 0, 'backgroundOnly' => 0],
        ];
        foreach($contents as $content) {
            $this->addSql('INSERT INTO `content`(`route`, `content`, `is_active`, `is_flash`, `background_only`) VALUES (:route, :content, :isActive, :isFlash, :backgroundOnly)', $content);
        }

        $routes = [
            'club', 
            'school_practices',
            'school_overview', 
            'school_operating',
            'school_equipment',
            'schedule',
            'registration_detail',
            'registration_membership_fee',
            'registration_tuto',
            'links',
            'contact', 
            'rules', 
            'legal_notices',
            'login_help',
            'default',
            'user_account',
        ];

        foreach($routes as $key => $route) {
            $this->addSql('UPDATE `content` SET `order_by`= :key WHERE `route` = :route', ['key' => $key, 'route' => $route]);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
