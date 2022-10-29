<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ContentRepository;
use Doctrine\Common\Collections\Collection;

class BackgroundService
{
    public function __construct(private ContentRepository $contentRepository)
    {
    }

    public function getDefaults(): Collection
    {
        return $this->contentRepository->findOneByRoute('default')?->getBackgrounds();
    }

    public function getDefault(): ?string
    {
        $defaults = $this->getDefaults();
    
        return (!$defaults->isEmpty()) ? '/images/background/landscape_xl/' . $defaults->first()->getFilename() : null;
    }
}
