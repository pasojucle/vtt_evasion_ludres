<?php

declare(strict_types=1);

namespace App\Mapper\Cluster;

use App\Dto\View\Cluster\ActivityView;
use App\Entity\BikeRide;
use App\Mapper\Activity\ActivityPeriodMapper;

class ClustersActivityMapper
{
    public function __construct(
        private ActivityPeriodMapper $activityPeriodMapper,
    ){}
    public function mapToView(BikeRide $bikeRide): ActivityView
    {
        return new ActivityView(
            id: $bikeRide->getId(),
            title: $bikeRide->getTitle(),
            period: $this->activityPeriodMapper->mapToView($bikeRide),
        );
    }
}