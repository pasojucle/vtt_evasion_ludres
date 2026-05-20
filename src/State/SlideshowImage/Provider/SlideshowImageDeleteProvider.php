<?php

declare(strict_types=1);

namespace App\State\SlideshowImage\Provider;

use App\Dto\DialogModalDto;
use App\Entity\SlideshowImage;
use App\Mapper\DestructiveModalMapper;

class SlideshowImageDeleteProvider
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    )
    {

    }
    public function mapToView(SlideshowImage  $entity): DialogModalDto
    {

        return $this->destructiveModalMapper->mapToView(sprintf('Etes vous certain de supprimer l\'image <b>%s</b> ?', $entity->getFilename()));
    }
}