<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230611162140 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
//         'SELECT s1.* FROM session s1
// inner join session s2 on s2.user_id=s1.user_id 
// INNER JOIN cluster AS c1 ON s1.cluster_id = c1.id
// INNER JOIN cluster AS c2 ON s2.cluster_id = c2.id and c2.bike_ride_id = c1.bike_ride_id
// where s1.id <> s2.id'
        $this->addSql('CREATE UNIQUE INDEX session_unique_idx ON session (user_id, cluster_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX session_unique_idx ON session');
    }
}
