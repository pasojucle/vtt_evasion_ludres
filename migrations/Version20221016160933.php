<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221016160933 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        $parameterGroupId = $this->connection->fetchOne('SELECT id FROM `parameter_group` WHERE `name` = \'EMAIL\'');

        if (false !== $parameterGroupId) {
            $parameterGroup = ['id' => $parameterGroupId, 'name' => 'MESSAGES', 'label' => 'Messages'];
            $this->addSql('UPDATE `parameter_group` SET `name` = :name, `label` = :label WHERE `id` = :id', $parameterGroup);

            $parameter = ['name' => 'MODAL_WINDOW_ORDER_IN_PROGRESS', 'parameterGroupId' => $parameterGroupId];
            $this->addSql('UPDATE `parameter` SET `parameter_group_id` = :parameterGroupId WHERE `name` = :name', $parameter);
        }

        $parameterGroupId = $this->connection->fetchOne('SELECT id FROM `parameter_group` WHERE `name` = \'REGISTRATION_CERTIFICATE\'');
        if (false !== $parameterGroupId) {
            $parameterGroup = ['id' => $parameterGroupId, 'name' => 'CERTIFICATES', 'label' => 'Attestations'];
            $this->addSql('UPDATE `parameter_group` SET `name` = :name, `label` = :label WHERE `id` = :id', $parameterGroup);

            $parameter = [
                'content' => '<p>Afin de permettre l&rsquo;accueil d&rsquo;un plus grand nombre de jeunes dans les clubs, la F&eacute;d&eacute;ration fran&ccedil;aise de cyclotourisme a cr&eacute;&eacute; le statut &laquo; d&rsquo;adulte accompagnateur &raquo;. Ce dispositif est un moyen pour inciter la personne &agrave; s&rsquo;engager dans le processus de formation f&eacute;d&eacute;rale (animateur &ndash; initiateur &ndash; moniteur). Une demande de contr&ocirc;le de l&rsquo;honorabilit&eacute; sera demand&eacute;e aux services de l&rsquo;Etat.<br /><strong>Conditions :</strong></p><ul><li>&Ecirc;tre licenci&eacute; au club ;</li><li>Pratiquer r&eacute;guli&egrave;rement l&rsquo;activit&eacute; route / VTT ;</li><li>&Ecirc;tre motiv&eacute; par l&rsquo;encadrement des activit&eacute;s jeunes.</li></ul><p><strong>Comp&eacute;tences :</strong></p><ul><li>&Ecirc;tre attentif au comportement des jeunes ;</li><li>&Ecirc;tre capable de v&eacute;rifier le v&eacute;lo avant de partir en randonn&eacute;e ;</li><li>&Ecirc;tre capable de transmettre les consignes de s&eacute;curit&eacute; en randonn&eacute;e.</li></ul><p><strong>Comportement attendu :</strong></p><ul><li>Accompagner le groupe avec vigilance ;</li><li>Avoir un comportement irr&eacute;prochable (respect des r&egrave;gles, des jeunes et des consignes) ;</li><li>Veiller au partage de l&rsquo;espace (route et chemins) ;</li><li>Utiliser un v&eacute;lo conforme aux prescriptions du Code de la route ;</li><li>Porter un casque r&eacute;glementaire.</li></ul><p><strong>D&eacute;roulement des activit&eacute;s :</strong></p><ul><li>L&rsquo;adulte accompagnateur applique les consignes dict&eacute;es par l&rsquo;animateur, l&#39;&eacute;ducateur ou le pr&eacute;sident de club (horaires, d&eacute;roulement de la sortie, exercices, s&eacute;curit&eacute;, etc.).</li></ul><p>L&rsquo;attestation est d&eacute;livr&eacute;e pour l&rsquo;ann&eacute;e par le pr&eacute;sident du club apr&egrave;s avis du responsable des &eacute;ducateurs le cas &eacute;ch&eacute;ant.</p><p>&nbsp;</p><p>Syvain Kohler pr&eacute;sident du club VTT &Eacute;vasion Ludres N&deg; f&eacute;d&eacute;ral xxxx</p><p><strong>Atteste que</strong></p><p>{{ prenom_nom }}</p><p>N&eacute;(e) le {{ date_naissance }} &agrave; {{ lieu_naissance }}</p><p>Licenci&eacute; au club VTT &Eacute;vasion Ludres - Num&eacute;ro de licence {{ numero_licence }}</p><p>Remplit les conditions pour &ecirc;tre &laquo; adulte accompagnateur &raquo; et participer sous la responsabilit&eacute; d&rsquo;un animateur, d&#39;un &eacute;ducateur qualifi&eacute; (initiateur, moniteur, instructeur) ou du pr&eacute;sident du club le cas &eacute;ch&eacute;ant, &agrave; l&rsquo;encadrement des jeunes.</p><p>&nbsp;</p><p>Fait &agrave; Ludres le {{ date }}</p><table border="0" cellpadding="0" cellspacing="0" style="width:100%"><tbody><tr><td style="text-align:center; width:30%"><strong>Le pr&eacute;sident</strong></td><td style="text-align:center">&nbsp;</td><td style="text-align:center; width:30%"><strong>L&rsquo;&eacute;ducateur<br />de la structure jeunes</strong></td><td style="text-align:center">&nbsp;</td><td style="text-align:center; width:30%"><strong>L&rsquo;int&eacute;ress&eacute;</strong></td></tr><tr><td style="border-color:#aaaaaa; border-width: 2px; height:80px; text-align:center">Signature pr&eacute;sident</td><td style="text-align:center">&nbsp;</td><td style="border-color:#aaaaaa; border-width: 2px;text-align:center">Signature &eacute;ducateur</td><td style="text-align:center">&nbsp;</td><td style="border-color:#aaaaaa; border-width: 2px;text-align:center">Signature int&eacute;ress&eacute;</td></tr></tbody></table><p>&nbsp;</p>',
                'name' => 'ACCOMPANYING_ADULT_CERTIFICATE',
                'label' => 'Attestation adulte accompagateur',
                'type' => 1,
                'parameterGroup' => $parameterGroupId,
            ];
            $this->addSql('INSERT INTO `parameter`(`name`, `label`, `type`, `value`, `parameter_group_id`) VALUES (:name, :label, :type, :content, :parameterGroup)', $parameter);
        }

        $parameters = [
            [
                'content' => '<p>Je soussigné : Sylvain KOHLER</p><p>&nbsp;</p><p>Président de l\’association VTT Évasion Ludres</p><p>&nbsp;</p><br><p>&nbsp;</p><br><p>&nbsp;</p><p>Certifie que : {{ prenom_nom }}</p><p>&nbsp;</p><p>Demeurant : {{ adresse }}</p><p>&nbsp;</p><p>Est inscrit(e) pour la saison sportive : {{ saison }}</p><p>&nbsp;</p><p>sous le numéro de licence {{ numero_licence }} de la fédération française de cyclotourisme</p><p>&nbsp;</p><p>Et a versé une cotisation globale de : {{ montant }}</p><p>&nbsp;</p><br><p>&nbsp;</p><p>Cette attestation a été établie pour servir et valoir ce que de droit</p><p>&nbsp;</p><br><p>&nbsp;</p><br><p>&nbsp;</p><p>Fait à Ludres le {{ date }}</p><p>&nbsp;</p><p>Président</p><p>&nbsp;</p><p>Sylvain KOHLER</p><p>&nbsp;</p>',
                'name' => 'REGISTRATION_CERTIFICATE_ADULT',
            ],
            [
                'content' => '<p>Je soussigné : Sylvain KOHLER</p><p>&nbsp;</p><p>Président de l\’association VTT Évasion Ludres</p><p>&nbsp;</p><br><p>&nbsp;</p><br><p>&nbsp;</p><p>Certifie que : {{ prenom_nom_parent }}</p><p>&nbsp;</p><p>Demeurant : {{ adresse_parent }}</p><p>&nbsp;</p><p>A inscrit son enfant {{ prenom_nom_enfant }} pour la saison sportive : {{ saison }}</p><p>&nbsp;</p><p>sous le numéro de licence {{ numero_licence }}  de la fédération française de cyclotourisme</p><p>&nbsp;</p><p>Et a versé une cotisation globale de : {{ montant }}</p><p>&nbsp;</p><br><p>&nbsp;</p><p>Cette attestation a été établie pour servir et valoir ce que de droit</p><p>&nbsp;</p><br><p>&nbsp;</p><br><p>&nbsp;</p><p>Fait à Ludres le {{ date }}</p><p>&nbsp;</p><p>Président</p><p>&nbsp;</p><p>Sylvain KOHLER</p><p>&nbsp;</p>',
                'name' => 'REGISTRATION_CERTIFICATE_SCHOOL',
            ],
        ];
        foreach($parameters as $parameter) {
            $this->addSql('UPDATE `parameter` SET `value` = :content WHERE `name` = :name', $parameter);
        }

        $this->addSql('DELETE FROM `parameter_group` WHERE `name` = \'MODAL_WINDOW\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE FROM `parameter` WHERE `name` = \'ACCOMPANYING_ADULT_CERTIFICATE\'');
        $parameterGroupId = $this->connection->fetchOne('SELECT id FROM `parameter_group` WHERE `name` = \'CERTIFICATES\'');
        if (false !== $parameterGroupId) {
            $parameterGroup = ['id' => $parameterGroupId, 'name' => 'REGISTRATION_CERTIFICATE', 'label' => 'Attestations'];
            $this->addSql('UPDATE `parameter_group` SET `name` = :name, `label` = :label WHERE `id` = :id', $parameterGroup);
        }
    }
}
