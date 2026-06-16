<?php

declare(strict_types=1);

namespace App\State\Level\Provider;

use App\Dto\View\DialogModalView;
use App\Entity\Level;
use App\Mapper\DestructiveModalMapper;
use App\State\DialogProviderInterface;

class LevelDeleteProvider implements DialogProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }
    public function mapToView(object $entity): DialogModalView
    {
        /** @var Level $entity */
        return $this->destructiveModalMapper->mapToView(sprintf('Etes vous certain de supprimer le niveau %s', $entity->getTitle()));
    }
}
