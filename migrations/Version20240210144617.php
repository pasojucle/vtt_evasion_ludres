<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240210144617 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cluster ADD position INT DEFAULT NULL');
    }

    public function postUp(Schema $schema): void
    {
        $clusters = $this->connection->fetchAllAssociative('SELECT bike_ride.id AS bike_ride_id, bike_ride_type.need_framers AS need_framers, cluster.id AS cluster_id, cluster.role AS role, level.id AS level_id, level.order_by AS level_position FROM cluster INNER JOIN bike_ride ON bike_ride.id = cluster.bike_ride_id INNER JOIN bike_ride_type ON bike_ride_type.id = bike_ride.bike_ride_type_id LEFT JOIN level ON level.id = cluster.level_id ORDER BY bike_ride.id, cluster.id');
        $clustersByBikeRide = [];
        foreach ($clusters as $cluster) {
            $clustersByBikeRide[$cluster['bike_ride_id']][] = $cluster;
        }
        foreach($clustersByBikeRide as $clusters) {
            foreach($clusters as $key => $cluster) {
                $position = ($cluster['level_position']) ? $cluster['level_position'] : $key;
                if ($cluster['need_framers']) {
                    $position =  $cluster['level_position'] + 1;
                }
                if ('ROLE_FRAME' === $cluster['role']) {
                    $position = 0;
                }
                $clusterToUpdate = [
                    'id' => $cluster['cluster_id'],
                    'position' => $position,
                ];
                $this->connection->executeQuery('UPDATE `cluster` SET `position`=:position WHERE `id` = :id', $clusterToUpdate);
            } 
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cluster DROP position');
    }


}
