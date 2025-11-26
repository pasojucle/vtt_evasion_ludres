<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Enum\OrderLineStateEnum;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251126173937 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_line ADD state ENUM(\'in_stock\', \'on_order\', \'unavailable\') DEFAULT \'in_stock\' NOT NULL COMMENT \'(DC2Type:OrderLineState)\'');
    }

    public function postUp(Schema $schema): void
    {
        foreach($this->getStates() as $state) {
            $this->connection->executeQuery('UPDATE order_line SET state=:state WHERE available=:available', $state);
        }
        $this->connection->executeQuery('ALTER TABLE order_line DROP available');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_line ADD available TINYINT(1) DEFAULT NULL');
    }

    public function postDown(Schema $schema): void
    {
        foreach($this->getStates() as $state) {
            $this->connection->executeQuery('UPDATE order_line SET available=:available WHERE state=:state', $state);
        }
        $this->connection->executeQuery('ALTER TABLE order_line DROP state');
    }

    private function getStates(): array
    {
        return [
            ['available' => 0, 'state' => OrderLineStateEnum::UNAVAILABLE->value],
            ['available' => 1, 'state' => OrderLineStateEnum::IN_STOCK->value],
        ];

    }
}
