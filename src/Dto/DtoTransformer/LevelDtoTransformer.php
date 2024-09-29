<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\LevelDto;
use App\Entity\Level;
use App\Service\LevelService;

class LevelDtoTransformer
{
    public function __construct(
        private readonly LevelService $levelService
    )
    {
        
    }

    public function fromEntity(?Level $level): LevelDto
    {
        $levelDto = new LevelDto();
        if ($level) {
            $levelDto->title = $level->getTitle();
            $levelDto->type = $level->getType();
            $levelDto->entity = $level;
            $levelDto->colors = $this->levelService->getColors($level->getColor());
            $levelDto->accompanyingCertificat = $level->isAccompanyingCertificat();
        }

        return $levelDto;
    }
}
