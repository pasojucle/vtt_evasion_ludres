<?php

declare(strict_types=1);

namespace App\DataFixtures\Common;

use App\Entity\Level;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;

class LevelFixtures extends AbstractFixture implements FixtureGroupInterface
{
    public const LEVEL_LITTLE_WOLVES = 'level_little_wolves';
    public const LEVEL_CHAMOIS = 'level_chamois';
    public const LEVEL_IBEX = 'level_ibex';
    public const LEVEL_LYNX = 'level_lynx';
    public const LEVEL_FOXES = 'level_foxes';
    public const LEVEL_GUIDE = 'level_guide';
    public const LEVEL_CLUB_LEADER = 'level_club_leader';
    public const LEVEL_INSTRUCTOR = 'level_instructor';
    public const LEVEL_MONITOR = 'level_monitor';
    public const LEVEL_EVALUATION_IN_PROGRESS = 'level_evaluation_in_progress';
    public const LEVEL_ADULT = 'level_adult';

    public const LEVELS = [
        self::LEVEL_LITTLE_WOLVES => ['Les Petits Loups', '8 à 10 ans et adultes débutants Randonnées de 5 à 10 Kms et cours de maniabilité, de sécurité.', '#fafa00', '1', '1', '0', '0', '0'],
        self::LEVEL_CHAMOIS => ['Les Chamois', '12 à 14 ans et adultes confirmés Randonnées de 15 à 20 Kms, contrôle de vitesse, orientation, cartographie, mécanique, etc...', '#1eff00', '3', '1', '0', '0', '0'],
        self::LEVEL_IBEX => ['Les Bouquetins', '15 ans et adultes sportifs confirmés. Diverses activités, vérifications des acquis et notamment sur la sécurité, recherche de l\'autonomie en orientation et mécanique, sorties xcountry de 20 à 30 kms', '#fa50aa', '4', '1', '0', '0', '0'],
        self::LEVEL_LYNX => ['Les Lynx', '15 ans et adultes techniquement confirmés Multiples activités de VTT, sorties très techniques, VTT parc.', '#75c3ff', '5', '1', '0', '0', '0'],
        self::LEVEL_FOXES => ['Les Renards', '10 à 12 ans et adultes débutants Randonnées de 10 à 15 Kms, contrôle de vitesse, orientation, cartographie, mécanique, etc...', '#ff9d5c', '2', '1', '0', '0', '0'],
        self::LEVEL_GUIDE => ['Accompagnateur', 'Adulte Accompagnateur', '#f5f5f5', '0', '2', '0', '0', '1'],
        self::LEVEL_CLUB_LEADER => ['Animateur club', 'Animateur club', '#dbdbdb', '1', '2', '0', '0', '0'],
        self::LEVEL_INSTRUCTOR => ['Initiateur', 'Initiateur', '#f7eafa', '2', '2', '0', '0', '0'],
        self::LEVEL_MONITOR => ['Moniteur', 'Moniteur', '#e0b8df', '3', '2', '0', '0', '0'],
        self::LEVEL_EVALUATION_IN_PROGRESS => ['Évaluation en cours', 'Nouveaux adhérents', '#df82f2', '0', '1', '1', '0', '0'],
        self::LEVEL_ADULT => ['Adulte hors encadrement', 'Adulte hors encadrement', null, '6', '3', '1', '0', '0'],
    ];

    public const SCHOOL_LEVELS = [
        self::LEVEL_LITTLE_WOLVES,
        self::LEVEL_CHAMOIS,
        self::LEVEL_IBEX,
        self::LEVEL_LYNX,
        self::LEVEL_FOXES,
    ];

    public static function getGroups(): array
    {
        return ['test'];
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::LEVELS as $ref => [$title, $content, $color, $orderBy, $type, $isProtected, $isDeleted, $accompanyingCertificat]) {
            $existing = $manager->getRepository(Level::class)
                ->findOneBy(['title' => $title]);

            if ($existing) {
                $this->addReference($ref, $existing);
                continue;
            }

            $level = new Level();
            $level->setTitle($title)
                ->setContent($content)
                ->setColor($color)
                ->setOrderBy((int) $orderBy)
                ->setType((int) $type)
                ->setIsProtected((bool) $isProtected)
                ->setIsDeleted((bool) $isDeleted)
                ->setAccompanyingCertificat((bool) $accompanyingCertificat);

            $manager->persist($level);
            $this->addReference($ref, $level);
        }

        $manager->flush();
    }

    public static function getLevelTitleFromReference(string $reference): string
    {
        if (array_key_exists($reference, self::LEVELS)) {
            return self::LEVELS[$reference][0];
        }

        throw new Exception("Référence inconnue");
    }
}
