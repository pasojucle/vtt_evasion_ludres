<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210823182053 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE parameter (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, label VARCHAR(150) NOT NULL, type INT NOT NULL, value LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('INSERT INTO `parameter`(`name`, `label`, `type`, `value`) VALUES (\'EVENT_SCHOOL_CONTENT\', \'ÉcoleVTT\', 1, \'Ecole VTT sur Ludres de 14h00 précise à 17h00, école VTT, rando et goûter.\'), (\'EVENT_ADULT_CONTENT\', \'Rando adultes et ados\', 1, "1er groupe (40 à 50 km) rendez-vous à 8h30 sur le parking de la Jauffaite, en face de l\'école Jacques Prévert ou à 9h00 au club sur le plateau de Ludres.2e et 3e groupe (20 à 40 km). Rendez-vous à 9h00 au club sur le plateau.")');
    
        $events = $this->connection->fetchAllAssociative('SELECT e.id AS event_id, e.type, c.id AS cluster_id, c.title AS cluster_title, s.id AS session_id FROM `event` AS e
        LEFT JOIN cluster AS c ON c.event_id = e.id
        LEFT JOIN session AS s ON s.cluster_id = c.id');

        $eventsByType = [];
        if (!empty($events)) {
            foreach($events as $event) {
                if ((int) $event['type'] !== 2 ) {
                    if (null !== $event['cluster_title'] && strtolower('1er groupe') == strtolower($event['cluster_title'])) {
                        $eventsByType[$event['event_id']]['cluster'] = $event['cluster_id'];
                    } elseif (null !== $event['session_id']) {
                        $eventsByType[$event['event_id']]['sessions_tmp'][] = $event['session_id'];
                    }
                }
                if ((int) $event['type'] === 2 ) {
                    if (null !== $event['cluster_title'] && 'Encadrement' === $event['cluster_title']) {
                        $eventsByType[$event['event_id']]['cluster'] = $event['cluster_id'];
                    } elseif (in_array($event['cluster_title'], ['Adulte expérimenté', 'Initiateur', 'Moniteur']) && null !== $event['session_id']) {
                        $eventsByType[$event['event_id']]['sessions_tmp'][] = $event['session_id'];
                    }
                }
            }
            foreach($eventsByType as $event) {
                if (array_key_exists('sessions_tmp', $event)) {
                    foreach($event['sessions_tmp'] as $sessionId) {
                        $session = ['sessionId' => $sessionId, 'clusterId' => $event['cluster']];
                        $this->addSql('UPDATE `session` SET `cluster_id`= :clusterId WHERE `id` = :sessionId', $session);
                    }
                }
            }
            $this->addSql("DELETE FROM `cluster` WHERE title in ('Adulte expérimenté', 'Initiateur', 'Moniteur', '2e groupe', '3e groupe')");
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE parameter');
    }
}
