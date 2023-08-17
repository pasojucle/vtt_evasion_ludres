<?php

declare(strict_types=1);

namespace App\UseCase\User;

use DateTime;
use App\Entity\User;
use App\Dto\BikeRideDto;
use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Dto\DtoTransformer\SessionDtoTransformer;
use App\Repository\SessionRepository;

class GetBikeRides
{
    public function __construct(
        private SessionDtoTransformer $sessionDtoTransformer,
        private SessionRepository $sessionRepository
    )
    {
        
    }


    public function execute(User $user): array
    {
        $bikeRides = [];
        $today = new DateTime();

        $sessions = $this->sessionDtoTransformer->fromEntities($user->getSessions());
        foreach ($sessions as $session) {
            if ($today <= $session->bikeRide->startAt->setTime(14, 0, 0)) {
                $bikeRides[] = [
                    'bikeRide' => $session->bikeRide,
                    'availability' => $session->availability,
                    'sessionId' => $session->id,
                    // 'memberList' => $session->getBikeRideMemberList($session->bikeRide),
                ];
            }
        }

        return $bikeRides;
    }

    public function getBikeRideMemberList(BikeRideDto $bikeRide): ?array
    {
        if ($bikeRide->bikeRideType->isShowMemberList) {
            $sessions = $this->sessionRepository->findByBikeRideId($bikeRide->id);
            foreach($sessions as $session) {

            }

            return $this->sessionDtoTransformer->fromEntities($sessions)->bikeRideMembers;
        }

        return null;
    }
}