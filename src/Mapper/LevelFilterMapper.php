<?php

declare(strict_types=1);

namespace App\Mapper;

use App\Entity\Enum\LevelType;

class LevelFilterMapper
{
    /**
     * Transforme le tableau mixte du formulaire en deux tableaux distincts (types et IDs)
     * * @param array $rawLevels Le tableau provenant de $filter->levels
     * @return array{0: LevelType[], 1: int[]} Tuple [$levelTypes, $levels]
     */
    public function parseRawLevels(array $rawLevels): array
    {
        $levelTypes = [];
        $levels = [];

        foreach ($rawLevels as $level) {
            if (is_string($level)) {
                $levelType = LevelType::tryFrom($level);
                if ($levelType) {
                    $levelTypes[] = $levelType;
                    continue;
                }
            }
            $levels[] = (int) $level;
        }

        return [$levelTypes, $levels];
    }
}