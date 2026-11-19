<?php

declare(strict_types=1);

namespace App\UseCase\v2\Activity;

use App\Entity\BikeRide;
use App\Repository\Interface\BikeRideRepositoryInterface;
use App\Service\SoftDeleteService;

class RestoreActivity
{
    public function __construct(
        private BikeRideRepositoryInterface $activityRepository,
        private SoftDeleteService $softDeleteService,
    ) {
    }

    public function __invoke(BikeRide $activity): void
    {
        $this->softDeleteService->restore($activity);
        
        $this->activityRepository->save($activity);
    }
}
