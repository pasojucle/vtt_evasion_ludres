<?php

declare(strict_types=1);

namespace App\State\Activity\Provider;

use App\Dto\DialogModalDto;
use App\Entity\BikeRide;
use App\Mapper\DestructiveModalMapper;
use App\Service\BikeRideService;


class ActivityDeleteProvider
{
    public function __construct(
        private DestructiveModalMapper $destructiveModalMapper,
        private BikeRideService $bikeRideService,
    )
    {

    }
    public function mapToView(BikeRide $entity): DialogModalDto
    {

        return $this->destructiveModalMapper->mapToView(
            sprintf('<p>Etes vous certain de supprimer<br>la sortie %s du %s', 
                $entity->getTitle(), 
                $this->bikeRideService->getPeriod($entity)
            )
        );
    }
}