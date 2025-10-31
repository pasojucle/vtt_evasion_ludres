<?php

namespace App\Service;

use App\Entity\Level;
use App\Repository\LevelRepository;
use Doctrine\Common\Collections\Collection;

class LevelService
{
    public const FILTER_TYPE_MEMBER = 1;
    public const FILTER_TYPE_REGISTRATION = 2;
    public const FILTER_TYPE_COVERAGE = 3;
    public const LEVEL_GROUP_SCHOOL = 'École VTT';
    public const LEVEL_GROUP_FRAME = 'Encadrement';
    public const LEVEL_ALL_MEMBER = 'Toute l\'école VTT';
    public const LEVEL_ALL_FRAME = 'Tout l\'encadrement';


    public function __construct(private LevelRepository $levelRepository)
    {
    }

    public function getLevelChoices(): array
    {
        $levelChoices = [];

        $levelTypes = [Level::TYPE_ALL_MEMBER, Level::TYPE_ALL_FRAME];
        $this->addLevelTypes($levelTypes, $levelChoices);

        $levels = $this->levelRepository->findAll();
        $this->addLevels($levels, $levelChoices);

        $levelChoices['Membres du bureau et comité'] = Level::TYPE_BOARD_MEMBER;

        return $levelChoices;
    }

    private function addLevels(Collection|array $levels, array &$array): void
    {
        foreach ($levels as $level) {
            match ($level->getType()) {
                Level::TYPE_SCHOOL_MEMBER => $array[self::LEVEL_GROUP_SCHOOL][$level->getTitle()] = $level->getId(),
                Level::TYPE_FRAME => $array[self::LEVEL_GROUP_FRAME][$level->getTitle()] = $level->getId(),
                default => $array[$level->getTitle()] = $level->getId()
            };
        }
    }

    private function addLevelTypes(Collection|array $levelTypes, array &$array): void
    {
        foreach ($levelTypes as $levelType) {
            if (Level::TYPE_ALL_MEMBER === $levelType) {
                $array[self::LEVEL_GROUP_SCHOOL] = [self::LEVEL_ALL_MEMBER => Level::TYPE_ALL_MEMBER];
            }
            if (Level::TYPE_ALL_FRAME === $levelType) {
                $array[self::LEVEL_GROUP_FRAME] = [self::LEVEL_ALL_FRAME => Level::TYPE_ALL_FRAME];
            }
        }
    }

    public function getLevelsAndTypesToStr(): array
    {
        $levels = [
            Level::TYPE_ALL_MEMBER => self::LEVEL_ALL_MEMBER,
            Level::TYPE_ALL_FRAME => self::LEVEL_ALL_FRAME,
        ];

        /** @var Level $level */
        foreach ($this->levelRepository->findAll() as $level) {
            $levels[$level->getId()] = $level->getTitle();
        }

        return $levels;
    }

    public function getColors(?string $color): ?array
    {
        if ($color) {
            $background = $color;
            list($r, $g, $b) = sscanf($background, '#%02x%02x%02x');
            $color = (0.3 * $r + 0.59 * $g + 0.11 * $b > 200) ? '#000' : '#fff';

            return ['color' => $color, 'background' => $background];
        }

        return null;
    }
}
