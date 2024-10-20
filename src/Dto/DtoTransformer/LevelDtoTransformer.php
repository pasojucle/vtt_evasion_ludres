<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\LevelDto;
use App\Entity\Level;
use App\Service\LevelService;
use Doctrine\Common\Collections\Collection;
use App\Dto\DtoTransformer\DtoTransformerInterface;

class LevelDtoTransformer implements DtoTransformerInterface
{
    public function __construct(
        private readonly LevelService $levelService
    )
    {
        
    }

    public function fromEntity($level): LevelDto
    {
        $levelDto = new LevelDto();
        if ($level) {
            $levelDto->id = $level->getId();
            $levelDto->title = $level->getTitle();
            $levelDto->type = $level->getType();
            $levelDto->entity = $level;
            $levelDto->colors = $this->levelService->getColors($level->getColor());
            $levelDto->accompanyingCertificat = $level->isAccompanyingCertificat();
        }

        return $levelDto;
    }


    public function fromEntities(iterable $levelEntities): array
    {
        $levels = [];
        foreach ($levelEntities as $levelEntity) {
            $levels[] = $this->fromEntity($levelEntity);
        }

        return $levels;
    }
}
