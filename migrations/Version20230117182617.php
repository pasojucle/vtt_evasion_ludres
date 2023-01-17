<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\RegistrationStep;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230117182617 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE health ADD content LONGTEXT DEFAULT NULL');
        $registrationStep = [
            'id' => 6,
            'content' => '<p>Si vous souhaitez nous signaller une pathologie qui pourrais se manifester durant de d&eacute;roulement d&#39;une sortie en vtt, remplissez le champ ci-dessous avec le nom de la pathologie ainsi la marche &agrave; suivre. Cette information restera confidentielle, et sera transmise uniquement aux encadrants afin qu&#39;ils puissent r&eacute;agir le cas &eacute;ch&eacute;ant, avec une action appropi&eacute;e.</p><p>Laissez ce champ vide dans le cas contraire.</p>',
            'testingRender' => RegistrationStep::RENDER_NONE,
            'finalRender' => RegistrationStep::RENDER_VIEW,
        ];

        $this->addSql('UPDATE `registration_step` SET `content`= :content, `testing_render`= :testingRender , `final_render`= :finalRender WHERE `id` = :id', $registrationStep);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE health DROP content');
    }
}
