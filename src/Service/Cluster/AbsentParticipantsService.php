<?php

declare(strict_types=1);

namespace App\Service\Cluster;

use App\Entity\BikeRide;
use App\Entity\Cluster;
use App\Entity\Enum\LevelType;
use App\Entity\Enum\RegistrationEnum;



class AbsentParticipantsService
{
    public function __invoke(Cluster $cluster): array
    {
        /** @var BikeRide $bikeRide */
        $bikeRide = $cluster->getBikeRide();
        $absentParticipants = [];
        if (!$cluster->isComplete() && RegistrationEnum::SCHOOL === $bikeRide->getBikeRideType()->getRegistration()) {
            foreach ($cluster->getSessions() as $session) {
                $level = $session->getMember()->getLevel();
                $levelType = (null !== $level) ? $level->getType() : LevelType::SCHOOL;
                if (!$session->isPresent() && LevelType::SCHOOL === $levelType) {
                    $absentParticipants[] = $session->getMember();
                }
            }
        }

        return $absentParticipants;
    }
}