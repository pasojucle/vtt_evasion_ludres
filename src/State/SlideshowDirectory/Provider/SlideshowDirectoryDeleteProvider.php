<?php

declare(strict_types=1);

namespace App\State\SlideshowDirectory\Provider;

use App\Dto\DialogModalDto;
use App\Entity\SlideshowDirectory;
use App\Mapper\DestructiveModalMapper;

class SlideshowDirectoryDeleteProvider
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    )
    {

    }
    public function mapToView(SlideshowDirectory  $entity): DialogModalDto
    {

        return $this->destructiveModalMapper->mapToView(sprintf('Etes vous certain de supprimer le répetroire <b>%s</b> ?', $entity->getName()));
    }
}