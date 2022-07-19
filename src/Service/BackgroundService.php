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

    public function getDefault(): Collection
    {
        return $this->contentRepository->findOneByRoute('default')?->getBackgrounds();
    }
}
