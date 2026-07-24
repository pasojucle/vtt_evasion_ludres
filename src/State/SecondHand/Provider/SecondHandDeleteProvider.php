<?php

declare(strict_types=1);

namespace App\State\SecondHand\Provider;

use App\Dto\View\DialogModalView;
use App\Entity\SecondHand;
use App\Mapper\DestructiveModalMapper;
use App\State\Interface\FormComponentProviderInterface;

class SecondHandDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }
    public function getFormView(object $entity, ?string $fallback = null): DialogModalView
    {
        /** @var SecondHand $entity */
        return $this->destructiveModalMapper->mapToView(
            sprintf('Etes vous certain de supprimer l\'annonce %s ?', $entity->getName()),
        );
    }
}
