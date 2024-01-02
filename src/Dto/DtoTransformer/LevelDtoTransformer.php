<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\LevelDto;
use App\Entity\Level;

class LevelDtoTransformer
{
    public function fromEntity(?Level $level): LevelDto
    {
        $levelDto = new LevelDto();
        if ($level) {
            $levelDto->title = $level->getTitle();
            $levelDto->type = $level->getType();
            $levelDto->entity = $level;
            $levelDto->colors = $this->getColors($level->getColor());
            $levelDto->accompanyingCertificat = $level->isAccompanyingCertificat();
        }

        return $levelDto;
    }

    private function getColors(?string $color): ?array
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
