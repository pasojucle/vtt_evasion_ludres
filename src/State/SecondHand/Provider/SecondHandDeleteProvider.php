<?php

declare(strict_types=1);

namespace App\State\SecondHand\Provider;

use App\Dto\View\DialogModalView;
use App\Entity\SecondHand;
use App\Mapper\DestructiveModalMapper;
use App\State\FormComponentProviderInterface;

class SecondHandDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }
    public function mapToView(object $entity): DialogModalView
    {
        /** @var SecondHand $entity */
        return $this->destructiveModalMapper->mapToView(
            sprintf('Etes vous certain de supprimer l\'annonce %s ?', $entity->getName()),
        );
    }
}
