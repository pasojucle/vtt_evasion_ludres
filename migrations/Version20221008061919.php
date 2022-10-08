<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221008061919 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE link ADD content LONGTEXT DEFAULT NULL');
        $this->addSql('DELETE FROM `link`WHERE `position` = 1');
        $links = [
            [
                'url' => 'https://www.chainreactioncycles.com/fr/fr',
                'title' => 'Chain Reaction',
                'description' => 'Détaillant en ligne de produits de cyclisme.',
                'image' => 'https://logovectordl.com/wp-content/uploads/2021/10/chain-reaction-cycles-logo-vector.png',
                'position' => 1,
                'orderBy' => 1,
                'content' => null,
            ], 
            [
                'url' => 'http://www.troc-velo.com',
                'title' => 'Troc-Vélo',
                'description' => 'Troc-Vélo', 'Partagez, Vendez, Achetez Votre Matériel Vélo',
                'image' => 'logo-troc-velo-62447c0197080.jpg',
                'position' => 1,
                'orderBy' => 2,
                'content' => '<p>Troc V&eacute;lo est le N&deg;1 des annonces v&eacute;lo : v&eacute;lo occasion, vtt et accessoires v&eacute;lo, toutes pi&egrave;ces v&eacute;lo. Vente et achat v&eacute;lo route et vtt d&#39;occasion, vtt &eacute;lectrique, v&eacute;lo urbain, troc et bonnes affaires pour les cyclistes passionn&eacute;s. Destockage des professionnels du cycle et magasin v&eacute;lo. D&eacute;posez vos annonces et vendez votre v&eacute;lo et vos &eacute;quipements cycliste c&#39;est rapide et gratuit !</p>',
            ], [
                'url' => 'http://www.fizzbikes.com',
                'title' => 'Fizzbikes',
                'description' => null,
                'image' => 'Fizzbike1-60e1d0569f4d3.png',
                'position' => 1,
                'orderBy' => 3,
                'content' => null,
            ], [
                'url' => 'http://www.bfzcycles.be',
                'title' => 'bfz cycles',
                'description' => 'Matériel vélo',
                'image' => 'www-bfzcycles-be-logo-60e1d8361ed76.jpg',
                'position' => 1,
                'orderBy' => 4,
                'content' => null,
            ], [
                'url' => 'http://www.velo101.com',
                'title' => 'Vélo 101',
                'description' => 'Magazine d\'actualité sur le cyclisme.',
                'image' => 'logo-63411a6811d6c.png',
                'position' => 1,
                'orderBy' => 5,
                'content' => '<p>V&eacute;lo 101 est le site francophone num&eacute;ro 1 sur le cyclisme sur route (pros, amateurs, cyclosport), vtt, piste et cyclo-cross. Actualit&eacute;s, directs, interviews, forums, tests de mat&eacute;riel, vid&eacute;os...</p>',
            ], [
                'url' => 'http://www.probikeshop.fr',
                'title' => 'Probikeshop',
                'description' => 'Magasin de Vélo en Ligne & Pièce Vélo',
                'image' => 'probikeshop-60e1dad7ed0af.svg',
                'position' => 1,
                'orderBy' => 6,
                'content' => '<p>Pi&egrave;ces v&eacute;lo, v&eacute;los complets, roues, pneus, textile 🚴&zwj;♀️ tout est dans la boutique Probikeshop 🚴 Des prix promo, du conseil, du stock et une livraison &eacute;clair !</p>',
            ], [
                'url' => 'https://www.alltricks.fr/',
                'title' => 'Alltricks',
                'description' => 'Avantages FFVélo  5 à 10 % de remise.',
                'image' => 'Logo-Alltricks-61191447d828c-613279d733a94.jpg',
                'position' => 1,
                'orderBy' => 0,
                'content' => '<p>&nbsp;Apres enregistrement de votre licence et cr&eacute;ation de compte aupr&egrave;s de la FFV&eacute;lo, vous aurez un message de la part d&#39;Alltricks, vous indiquant les diverses remises et conditions. Voir rubrique &quot;Mes messages&quot; dans votre compte Alltricks.</p>',
            ],
        ];
        foreach($links as $link) {
            $this->addSql('INSERT INTO `link` (`url`, `title`, `description`, `image`, `position`, `order_by`, `content`) VALUES (:url, :title, :description, :image, :position, :orderBy, :content)', $link);
        }
        
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE link DROP content');
    }
}
