<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231115184648 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE sworn_certification');
        $this->addSql('CREATE TABLE licence_sworn_certification (id INT AUTO_INCREMENT NOT NULL, licence_id INT NOT NULL, sworn_certification_id INT NOT NULL, value TINYINT(1) NOT NULL, INDEX IDX_25B43C0D26EF07C9 (licence_id), INDEX IDX_25B43C0D313364D9 (sworn_certification_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sworn_certification (id INT AUTO_INCREMENT NOT NULL, label LONGTEXT NOT NULL, school TINYINT(1) DEFAULT 0 NOT NULL, adult TINYINT(1) DEFAULT 0 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE licence_sworn_certification ADD CONSTRAINT FK_25B43C0D26EF07C9 FOREIGN KEY (licence_id) REFERENCES licence (id)');
        $this->addSql('ALTER TABLE licence_sworn_certification ADD CONSTRAINT FK_25B43C0D313364D9 FOREIGN KEY (sworn_certification_id) REFERENCES sworn_certification (id)');
        $this->addSql('ALTER TABLE licence DROP type');

        $swornCertifications = [
            [
                'label' => 'J\'ai bien pris note de ces questions et comprends que certaines situations ou symptômes peuvent entraîner un risque pour ma santé et/ou pour mes performances.',
                'isSchool' => 0,
                'isAdult' => 1,
            ],
            [
                'label' => 'J\'atteste sur l\'honneur avoir déjà pris, ou prendre les dispositions nécessaires selon les recommandations données en cas de réponse positive à l\'une des questions des différents questionnaires.',
                'isSchool' => 0,
                'isAdult' => 1,
            ],
            [
                'label' => 'Je fournis un certificat médical de moins de 6 mois (cyclotourisme) <b>OU</b> J\'atteste sur l\'honneur avoir renseigné le questionnaire de santé qui m\'a été remis par mon club.',
                'isSchool' => 1,
                'isAdult' => 0,
            ],
            [
                'label' => 'J\'atteste sur l\'honneur avoir répondu par la négative à toutes les rubriques du questionnaire de santé et je reconnais expressément que les réponses apportées relèvent de ma responsabilité exclusive.',
                'isSchool' => 1,
                'isAdult' => 0,
            ],
            [
                'label' => 'Je m\'engage à respecter scrupuleusement le Code de la route, les statuts et règlements de la Fédération française de cyclotourisme, ainsi que les statuts et les règlements du VTT Evasion Ludres consultable sur le site www.vttevasionludres.fr',
                'isSchool' => 1,
                'isAdult' => 1,
            ],
        ];

        foreach($swornCertifications as $swornCertification) {
            $this->addSql('INSERT INTO `sworn_certification`(`label`, `school`, `adult`) VALUES (:label, :isSchool, :isAdult)', $swornCertification);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE licence_sworn_certification DROP FOREIGN KEY FK_25B43C0D26EF07C9');
        $this->addSql('ALTER TABLE licence_sworn_certification DROP FOREIGN KEY FK_25B43C0D313364D9');
        $this->addSql('DROP TABLE licence_sworn_certification');
        $this->addSql('DROP TABLE sworn_certification');
        $this->addSql('ALTER TABLE licence ADD type INT DEFAULT NULL');
    }
}
