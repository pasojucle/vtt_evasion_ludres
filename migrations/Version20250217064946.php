<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Enum\OrderStatusEnum;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250217064946 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_header  ADD comments LONGTEXT DEFAULT NULL, CHANGE status status ENUM(\'in_progress\', \'ordered\', \'valided\', \'completed\', \'canceled\') NOT NULL COMMENT \'(DC2Type:OrderStatus)\'');
        $this->addSql('ALTER TABLE order_line ADD available TINYINT(1) DEFAULT NULL');

        $messages = $this->getMessages();
        foreach($messages as $message) {
            $this->addSql('INSERT INTO `message`(`section_id`, `name`, `label`, `content`, `level_type`, `protected`) VALUES ((SELECT `id` FROM `parameter_group` WHERE name LIKE :sectionId), :name, :label, :content, :levelType, :protected)', $message);
        }

        $message = [
            'name' => 'EMAIL_CONFIRMATION_SESSION_REGISTRATION_BIKE_RIDE',
            'content' => '<p>VOTRE COMMANDE EST VALIDÉE.</p><p><strong>Document &agrave; imprimer<br />et &agrave; envoyer accompagn&eacute; de votre r&eacute;glement<br />A l&#39;adresse suivante :<br />VTT Evasion Ludres<br />184 rue Saint Exup&eacute;ry - 54710 Ludres</strong></p>',
        ];
        $this->addSql('UPDATE message set content = :content WHERE name = :name', $message);

        $this->addSql('UPDATE order_line INNER JOIN order_header ON order_line.order_header_id = order_header.id SET order_line.available = :available WHERE order_header.status = :valided or order_header.status = :completed', ['available' => 1, 'valided' => OrderStatusEnum::VALIDED->value, 'completed' => OrderStatusEnum::COMPLETED->value]);
        $this->addSql('UPDATE order_line INNER JOIN order_header ON order_line.order_header_id = order_header.id SET order_line.available = :available WHERE order_header.status = :canceled', ['available' => 0, 'canceled' => OrderStatusEnum::CANCELED->value]);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_header DROP comments, CHANGE status status VARCHAR(255) NOT NULL COMMENT \'(DC2Type:OrderStatus\'');
        $this->addSql('ALTER TABLE order_line DROP available');
        $messages = $this->getMessages();
        foreach($messages as $message) {
            $this->addSql('DELETE FROM `message` WHERE name LIKE :name', $message);
        }
    }

    private function getMessages(): array
    {    
        return [
            [                
                'name' => 'ORDER_WAITING_VALIDATE_MESSAGE',
                'label' => 'Message afficher à la validationde commande par l\'adhérent',
                'content' => '<p>LES VÊTEMENTS AUX COULEURS DU CLUB SONT EN STOCK LIMITE. VOTRE COMMANDE POURRA ÊTRE MODIFIÉE OU ANNULÉE EN FONCTION DES STOCKS RESTANTS.</p><p>Vous recevrez une notification dès traitement de votre commande.</p><p><strong>Attendez la validation avant de nous faire parvenir le règlement.</strong></p>',
                'levelType' => null,
                'protected' => 1,
                'sectionId' => 'ORDER',
            ],
            [                
                'name' => 'MODAL_WINDOW_ORDER_VALIDED',
                'label' => 'Message du pop\'up pour une commande validée',
                'content' => '<p>Votre commande a été valid&eacute;e en fonction du stock disponible. Souhaitez-vous finaliser votre commande ?</p>',
                'levelType' => null,
                'protected' => 1,
                'sectionId' => 'ORDER',
            ],
            [                
                'name' => 'MODAL_WINDOW_ORDER_CANCELED',
                'label' => 'Message du pop\'up pour une commande annulée',
                'content' => '<p>Votre commande a été annulée. Souhaitez-vous consulter votre commande ?</p>',
                'levelType' => null,
                'protected' => 1,
                'sectionId' => 'ORDER',
            ],
        ];
    }
}
