<?php

declare(strict_types=1);

namespace App\UseCase\Dashboard;

use App\Entity\BikeRide;
use App\Entity\Enum\RegistrationEnum;
use App\Entity\Level;
use App\Entity\Session;
use App\Repository\SessionRepository;
use App\Service\LevelService;

class GetSchoolBikeRideClusters
{
    public function __construct(
        private SessionRepository $sessionRepository,
        private LevelService $levelService,
    ) {
    }

    public function execute(BikeRide $bikeRide): array
    {
        $isSchoolBikeRide = RegistrationEnum::SCHOOL === $bikeRide->getBikeRideType()->getRegistration();
        $sessionsByClusters = [];
        /** @var Session $session */
        foreach ($this->sessionRepository->findByBikeRideId($bikeRide->getId()) as $session) {
            $clusterId = ($isSchoolBikeRide && Level::TYPE_FRAME === $session->getMember()->getLevel()->getType())
                ? 'framers'
                : $session->getCluster()->getId();
            $sessionsByClusters[$clusterId][] = $session;
        }

        $clusters = [];
        foreach ($bikeRide->getClusters() as $clusterEntity) {
            $clusterId = ($isSchoolBikeRide && !$clusterEntity->getLevel())
                    ? 'framers'
                    : $clusterEntity->getId();
            $sessions = (array_key_exists($clusterId, $sessionsByClusters))
                ? $sessionsByClusters[$clusterEntity->getId()]
                : [];
            $clusters[] = [
                'title' => $clusterEntity->getTitle(),
                'level' => ['colors' => $this->levelService->getColors($clusterEntity->getLevel()?->getColor())],
                'sessions' => $sessions];
        }

        return $clusters;
    }
}
