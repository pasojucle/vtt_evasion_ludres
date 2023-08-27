<?php

declare(strict_types=1);

namespace App\UseCase\User;

use App\Dto\DtoTransformer\SessionDtoTransformer;
use App\Entity\Session;
use App\Entity\User;
use App\Service\SessionService;
use DateTime;

class GetBikeRides
{
    public function __construct(
        private SessionDtoTransformer $sessionDtoTransformer,
        private SessionService $sessionService,
    ) {
    }


    public function execute(User $user): array
    {
        $bikeRides = [];
        $today = new DateTime();

        /** @var Session $session */
        foreach ($user->getSessions() as $session) {
            $sessionDto = $this->sessionDtoTransformer->fromEntity($session);
            if ($today <= $sessionDto->bikeRide->startAt->setTime(14, 0, 0)) {
                $bikeRides[] = [
                    'bikeRide' => $sessionDto->bikeRide,
                    'availability' => $sessionDto->availability,
                    'sessionId' => $sessionDto->id,
                    'memberList' => $this->sessionService->getBikeRideMembers($session->getCluster()->getBikeRide())
                ];
            }
        }

        return $bikeRides;
    }
}
