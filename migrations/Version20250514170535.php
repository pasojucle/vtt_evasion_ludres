<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250514170535 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE survey_response FROM `survey_response` INNER JOIN survey_issue ON survey_issue.id = survey_response.survey_issue_id INNER JOIN survey ON survey.id = survey_issue.survey_id WHERE survey.id = 63 AND survey_response.value IS null AND survey_response.user_id IS null;');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
