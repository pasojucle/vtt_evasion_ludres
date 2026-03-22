<?php

declare(strict_types=1);

namespace App\UseCase\User;

use App\Dto\DtoTransformer\SessionDtoTransformer;
use App\Entity\Session;
use App\Entity\Member;
use App\Repository\SessionRepository;
use App\UseCase\Session\GetFormSession;

class GetBikeRides
{
    public function __construct(
        private SessionDtoTransformer $sessionDtoTransformer,
        private SessionRepository $sessionRepository,
        private GetFormSession $getFormSession,
    ) {
    }


    public function execute(Member $member): array
    {
        $bikeRides = [];

        /** @var Session $session */
        foreach ($this->sessionRepository->findAvailableByUser($member) as $session) {
            $sessionDto = $this->sessionDtoTransformer->fromEntity($session);
            $bikeRides[] = [
                'bikeRide' => $sessionDto->bikeRide,
                'availability' => $sessionDto->availability,
                'sessionId' => $sessionDto->id,
                'memberList' => $this->getFormSession->getBikeRideMembers($session->getCluster()->getBikeRide()),
                'practice' => $sessionDto->practice,
                'bikeType' => $sessionDto->bikeType,
                'cluster' => $sessionDto->cluster,
            ];
        }

        usort($bikeRides, function ($a, $b) {
            $a = $a['bikeRide']->startAt;
            $b = $b['bikeRide']->startAt;

            if ($a === $b) {
                return 0;
            }

            return ($a < $b) ? -1 : 1;
        });

        return $bikeRides;
    }
}
