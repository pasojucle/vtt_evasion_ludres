<?php

declare(strict_types=1);

namespace App\UseCase\User;

use App\Dto\DtoTransformer\SessionDtoTransformer;
use App\Entity\Session;
use App\Entity\User;
use App\Repository\SessionRepository;
use App\Service\SessionService;

class GetBikeRides
{
    public function __construct(
        private SessionDtoTransformer $sessionDtoTransformer,
        private SessionService $sessionService,
        private SessionRepository $sessionRepository,
    ) {
    }


    public function execute(User $user): array
    {
        $bikeRides = [];

        /** @var Session $session */
        foreach ($this->sessionRepository->findAvailableByUser($user) as $session) {
            $sessionDto = $this->sessionDtoTransformer->fromEntity($session);
            $bikeRides[] = [
                'bikeRide' => $sessionDto->bikeRide,
                'availability' => $sessionDto->availability,
                'sessionId' => $sessionDto->id,
                'memberList' => $this->sessionService->getBikeRideMembers($session->getCluster()->getBikeRide()),
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
