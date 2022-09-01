<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220828110524 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('UPDATE `registration_step` SET `title`=\'Questionnaire de santé Adulte\',`order_by`=3, `registration_step_group_id`=7, `final_render` = 3 WHERE `id`=3');
        $registrationStep['content'] = '<p style="text-align:justify">Je soussign&eacute; {{ prenom_nom_parent }} Inscrit et autorise l&#39;enfant {{ prenom_nom_enfant }} &agrave; participer aux s&eacute;ances p&eacute;dagogiques et &agrave; v&eacute;lo de l&rsquo;Ecole VTT Evasion Ludres.</p><p style="text-align:justify">J&rsquo;autorise &eacute;galement les moniteurs f&eacute;d&eacute;raux ainsi que les initiateurs f&eacute;d&eacute;raux ou tout autre futur moniteur Photo et/ou initiateur, &agrave; prendre toute d&eacute;cision concernant les soins d&rsquo;urgences qui s&rsquo;av&eacute;reraient n&eacute;cessaires obligatoire concernant cet enfant lors des activit&eacute;s organis&eacute;es par le club.</p><h2 style="text-align:justify">Retour au domicile</h2><p style="text-align:justify">J&rsquo;autorise mon enfant &agrave; rejoindre seul le domicile &agrave; l&rsquo;issue de la s&eacute;ance. (- 12 ans exclu de rejoindre seul) Pour ce faire je m&rsquo;engage &agrave; lui fournir un gilet jaune fluo ainsi qu&rsquo;un dispositif d&rsquo;&eacute;clairage avant (blanc) et arri&egrave;re (rouge)</p><p>{{ bouton_retour_domicile }}</p><h2 style="text-align:justify">Droit image</h2><p style="text-align:justify">Dans le cadre de nos activit&eacute;s, nous sommes amen&eacute;s &agrave; prendre des photos, des films ou des enregistrements sonores des membres du club pratiquant le VTT seul ou en groupe lors de nos sorties ou de diverses manifestations sportives. C&#39;est dans cet objectif que nous vous demandons l&rsquo;autorisation pour &ecirc;tre photographi&eacute;, film&eacute; ou enregistr&eacute;, et ce uniquement pour la communication de notre club. L&#39;association s&#39;interdit express&eacute;ment de proc&eacute;der &agrave; une exploitation des photographies, films et/ou interviews susceptibles de porter atteinte &agrave; la vie priv&eacute;e ou &agrave; la r&eacute;putation de ses adh&eacute;rents.<br />Je soussign&eacute;&nbsp;{{ prenom_nom_parent }} autorise le club VTT Evasion Ludres &agrave;&nbsp;utiliser ou faire utiliser ou reproduire ou faire reproduire l&#39;image et la voix de mon enfant {{ prenom_nom_enfant }}. Je me&nbsp; reconnais enti&egrave;rement rempli de mes droits et je ne pourrai pr&eacute;tendre &agrave; aucune r&eacute;mun&eacute;ration pour l&#39;exploitation des droits vis&eacute;s &agrave; la pr&eacute;sente.</p><p>{{ bouton_droit_image }}</p>';
        $this->addSql('UPDATE `registration_step` SET `content` = :content WHERE `id`=10',$registrationStep);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
