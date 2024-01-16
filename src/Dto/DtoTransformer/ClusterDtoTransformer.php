<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Entity\Level;
use App\Dto\ClusterDto;
use App\Entity\Cluster;
use App\Entity\Session;
use App\Entity\BikeRide;
use App\Entity\BikeRideType;
use App\Service\ClusterService;
use App\Repository\SessionRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class ClusterDtoTransformer
{
    public function __construct(
        private SessionDtoTransformer $sessionDtoTransformer,
        private LevelDtoTransformer $levelDtoTransformer,
        private ClusterService $clusterService,
        private UserDtoTransformer $userDtoTransformer,
        private SessionRepository $sessionRepository,
    ) {
    }

    public function fromEntity(Cluster $cluster, $sessionEntities = null): ClusterDto
    {
        $cachePool = new FilesystemAdapter();

        $clusterCache = $cachePool->getItem(sprintf('cluster.%s', $cluster->getId()));
        if (!$clusterCache->isHit()) {
            dump(sprintf(' set cache cluster.%s', $cluster->getId()));

            $fromEntities = true;
            if (!$sessionEntities) {
                $sessionEntities = $cluster->getSessions();
                $fromEntities = false;
            }

            $clusterDto = new ClusterDto();
            $clusterDto->id = $cluster->getId();
            $clusterDto->title = $cluster->getTitle();
            $clusterDto->level = $this->levelDtoTransformer->fromEntity($cluster->getLevel());
            $clusterDto->sessions = $this->getSessions($sessionEntities, $fromEntities);
            $clusterDto->maxUsers = $cluster->getMaxUsers();
            $clusterDto->role = $cluster->getRole();
            $clusterDto->isComplete = $cluster->isComplete();
            $clusterDto->memberSessions = $this->clusterService->getMemberSessions($cluster);
            $clusterDto->availableSessions = $this->getAvailableSessions($sessionEntities);
            $clusterDto->usersOnSiteCount = $this->getUsersOnSiteCount($sessionEntities, $cluster->getBikeRide());

            $clusterCache->set($clusterDto);
            $cachePool->save($clusterCache);
        }

        return $clusterCache->get();
    }
    
    public function fromBikeRide(BikeRide $bikeRide): array
    {
        $sessionsByClusters = [];
        /** @var Session $session */
        foreach ($this->sessionRepository->findByBikeRide($bikeRide) as $session) {
            $clusterId = $session->getCluster()->getId();
            $sessionsByClusters[$clusterId][] = $session;
        }

        $clusters = [];
        foreach ($bikeRide->getClusters() as $clusterEntity) {
            $sessions = (array_key_exists($clusterEntity->getId(), $sessionsByClusters)) ? $sessionsByClusters[$clusterEntity->getId()] : [];
            $clusters[] = $this->fromEntity($clusterEntity, new ArrayCollection($sessions));
        }

        return $clusters;
    }
    
    public function headerFromBikeRide(BikeRide $bikeRide): array
    {
        $clusters = [];
        foreach ($bikeRide->getClusters() as $clusterEntity) {
            $clusterDto = new ClusterDto();
            $clusterDto->id = $clusterEntity->getId();
            $clusterDto->title = $clusterEntity->getTitle();
            $clusters[] = $clusterDto;
        }

        return $clusters;
    }

    private function getSessions(Collection $sessionEntities, bool $fromEntities): array
    {
        $sessions = [];
        foreach ($sessionEntities as $session) {
            $sessions[] = [
                'user' => ($fromEntities)
                    ? $this->userDtoTransformer->getSessionHeaderFromEntity($session->getUser())
                    : $this->userDtoTransformer->fromEntity($session->getUser()),
                'availability' => $session->getAvailability(),
                'isPresent' => $session->isPresent(),
            ];
        }

        return $sessions;
    }

    private function getAvailableSessions(Collection $sessionEntities): ArrayCollection
    {
        $sortedSessions = [];

        /** @var Session $session */
        foreach ($sessionEntities as $session) {
            if (Session::AVAILABILITY_UNAVAILABLE !== $session->getAvailability()) {
                $sortedSessions[] = $this->sessionDtoTransformer->fromEntity($session);
            }
        }
        usort($sortedSessions, function ($a, $b) {
            $a = strtolower($a->user->member->name);
            $b = strtolower($b->user->member->name);

            if ($a === $b) {
                return 0;
            }

            return ($a < $b) ? -1 : 1;
        });

        return new ArrayCollection($sortedSessions);
    }

    private function getUsersOnSiteCount(Collection $sessionEntities, BikeRide $bikeRide): int
    {
        $userOnSiteSessions = [];
        foreach ($sessionEntities as $session) {
            if (BikeRideType::REGISTRATION_SCHOOL === $bikeRide->getBikeRideType()->getRegistration()) {
                $level = $session->getUser()->getLevel();
                $levelType = (null !== $level) ? $level->getType() : Level::TYPE_SCHOOL_MEMBER;
                if ($session->isPresent() && Level::TYPE_SCHOOL_MEMBER === $levelType) {
                    $userOnSiteSessions[] = $session;
                }
            } else {
                if ($session->isPresent()) {
                    $userOnSiteSessions[] = $session;
                }
            }
        }

        return count($userOnSiteSessions);
    }
}
