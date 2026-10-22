<?php

declare(strict_types=1);

namespace App\UseCase\v2\ActivityType;

use App\Entity\BikeRideType;
use App\Entity\Enum\RegistrationEnum;
use App\Repository\Interface\ActivityTypeRepositoryInterface;

class UpdateActivityType
{
    public function __construct(
        private ActivityTypeRepositoryInterface $activityTypeRepository,
    ) {
    }


    public function __invoke(BikeRideType $activityType): BikeRideType
    {
        if (RegistrationEnum::CLUSTERS !== $activityType->getRegistration()) {
            $activityType->setClusters([]);
        }

        $this->activityTypeRepository->save($activityType);

        return $activityType;
    }
}
