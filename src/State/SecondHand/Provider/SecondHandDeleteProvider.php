<?php

declare(strict_types=1);

namespace App\State\SecondHand\Provider;

use App\Dto\DialogModalView;
use App\Entity\SecondHand;
use App\Mapper\DestructiveModalMapper;

class SecondHandDeleteProvider
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }
    public function mapToView(SecondHand $entity): DialogModalView
    {
        return $this->destructiveModalMapper->mapToView(
            sprintf('Etes vous certain de supprimer l\'annonce %s ?', $entity->getName()),
        );
    }
}
