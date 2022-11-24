<?php

namespace App\Service;

use App\Entity\Level;
use App\Repository\LevelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class LevelService
{
    public const STATUS_TYPE_MEMBER = 1;
    public const STATUS_TYPE_REGISTRATION = 2;
    public const STATUS_TYPE_COVERAGE = 3;
    public const LEVEL_GROUP_SCHOOL = 'École VTT';
    public const LEVEL_GROUP_FRAME = 'Encadrement';

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

        return $levelChoices;
    }

    public function addLevels(Collection|array $levels, array &$array): void
    {
        foreach ($levels as $level) {
            match ($level->getType()) {
                Level::TYPE_SCHOOL_MEMBER => $array[self::LEVEL_GROUP_SCHOOL][$level->getTitle()] = $level->getId(),
                Level::TYPE_FRAME => $array[self::LEVEL_GROUP_FRAME][$level->getTitle()] = $level->getId(),
                default => $array[$level->getTitle()] = $level->getId()
            };
        }
    }

    public function addLevelTypes(Collection|array $levelTypes, array &$array): void
    {
        foreach ($levelTypes as $levelType) {
            if (Level::TYPE_ALL_MEMBER === $levelType) {
                $array[self::LEVEL_GROUP_SCHOOL] = ['Toute l\'école VTT' => Level::TYPE_ALL_MEMBER];
            }
            if (Level::TYPE_ALL_FRAME === $levelType) {
                $array[self::LEVEL_GROUP_FRAME] = ['Tout l\'encadrement' => Level::TYPE_ALL_FRAME];
            }
        }
    }
}
