<?php

declare(strict_types=1);

namespace App\State\Background\Provider;

use App\Dto\DialogModalDto;
use App\Entity\Background;
use App\Mapper\DestructiveModalMapper;


class BackgroundDeleteProvider
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    )
    {

    }
    public function mapToView(Background $entity): DialogModalDto
    {

        return $this->destructiveModalMapper->mapToView(
            sprintf('Etes vous certain de supprimer l\'image de fond %s', $entity->getFilename()),
        );
    }
}