<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\BackgroundDto;
use App\Entity\Background;
use App\Service\ProjectDirService;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;

class BackgroundDtoTransformer
{
    public function __construct(
        private ProjectDirService $projectDirService
    ) {
    }

    public function fromEntity(?Background $background): BackgroundDto
    {
        $backgroundDto = new BackgroundDto();
        if ($background) {
            $backgroundDto->id = $background->getId();
            $backgroundDto->filename = $background->getFilename();
            $backgroundDto->path = $this->getPath($background->getFilename());
        }
        
        return $backgroundDto;
    }


    public function fromEntities(Paginator|array|Collection $backgroundEntities): array
    {
        $background = [];
        foreach ($backgroundEntities as $backgroundEntity) {
            $background[] = $this->fromEntity($backgroundEntity);
        }

        return $background;
    }

    private function getPath(?string $fileName): ?string
    {
        return ($fileName) ? $this->projectDirService->path('backgrounds', $fileName) : null;
    }
}
