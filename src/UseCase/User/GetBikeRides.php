<?php

declare(strict_types=1);

namespace App\UseCase\User;

use App\Dto\DtoTransformer\SessionDtoTransformer;
use App\Entity\User;
use App\Repository\SessionRepository;
use DateTime;

class GetBikeRides
{
    public function __construct(
        private SessionDtoTransformer $sessionDtoTransformer,
        private SessionRepository $sessionRepository
    ) {
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
                ];
            }
        }

        return $bikeRides;
    }
}
