<?php

declare(strict_types=1);

namespace App\State\Activity\Provider;

use App\Dto\DialogModalView;
use App\Entity\BikeRide;
use App\Mapper\DestructiveModalMapper;
use App\Service\BikeRideService;
use App\State\DialogProviderInterface;

class ActivityDeleteProvider implements DialogProviderInterface
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
        private BikeRideService $bikeRideService,
    ) {
    }
    public function mapToView(object $entity): DialogModalView
    {
        assert($entity instanceof BikeRide);
        
        return $this->destructiveModalMapper->mapToView(
            sprintf(
                '<p>Etes vous certain de supprimer<br>la sortie %s du %s',
                $entity->getTitle(),
                $this->bikeRideService->getPeriod($entity)
            )
        );
    }
}
