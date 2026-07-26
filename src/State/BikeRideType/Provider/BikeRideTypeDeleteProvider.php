<?php

declare(strict_types=1);

namespace App\State\BikeRideType\Provider;

use App\Dto\View\DialogModalView;
use App\Entity\BikeRideType;
use App\Mapper\DestructiveModalMapper;
use App\State\Interface\FormComponentProviderInterface;

class BikeRideTypeDeleteProvider implements FormComponentProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
    ) {
    }

    /**
     * @param BikeRideType $entity
     * @implements FormComponentProviderInterface<BikeRideType>
     */
    public function getFormView(object $entity, ?string $fallback = null): DialogModalView
    {
        return $this->destructiveModalMapper->mapToView(
            sprintf('Etes vous certain de supprimer le type d\'activité %s', $entity->getName())
        );
    }

    public function getFormOptions(object $entity): array
    {
        return [];
    }
}
