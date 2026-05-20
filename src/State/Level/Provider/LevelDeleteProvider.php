<?php

declare(strict_types=1);

namespace App\State\Level\Provider;

use App\Dto\DialogModalDto;
use App\Entity\Level;
use App\Mapper\DestructiveModalMapper;

class LevelDeleteProvider
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    )
    {

    }
    public function mapToView(Level  $entity): DialogModalDto
    {

        return $this->destructiveModalMapper->mapToView(sprintf('Etes vous certain de supprimer le niveau %s', $entity->getTitle()));
    }
}