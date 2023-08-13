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
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Common\Collections\ArrayCollection;

class ClusterDtoTransformer
{
    public function __construct(
        private SessionDtoTransformer $sessionDtoTransformer,
        private ClusterService $clusterService
    )
    {
        
    }

    public function fromEntity(Cluster $cluster): ClusterDto
    {
        $sessionEntities = $cluster->getSessions();

        $clusterDto = new ClusterDto;
        $clusterDto->id = $cluster->getId();
        $clusterDto->entity = $cluster;
        $clusterDto->title = $cluster->getTitle();
        $clusterDto->level = $cluster->getLevel();
        $clusterDto->sessions = $this->getSessions($sessionEntities);
        $clusterDto->maxUsers = $cluster->getMaxUsers();
        $clusterDto->role = $cluster->getRole();
        $clusterDto->isComplete = $cluster->isComplete();
        $clusterDto->memberSessions = $this->clusterService->getMemberSessions($cluster);
        $clusterDto->availableSessions = $this->getAvailableSessions($sessionEntities);
        $clusterDto->usersOnSiteCount = $this->getUsersOnSiteCount($sessionEntities, $cluster->getBikeRide());

        return $clusterDto;
    }
    
    public function fromEntities(Paginator|Collection|array $clusterEntities): array
    {
        $clusters = [];
        foreach($clusterEntities as $clusterEntity) {
            $clusters[] = $this->fromEntity($clusterEntity);
        }

        return $clusters;
    }

    private function getSessions(Collection $sessionEntities): array
    {
        $sessions = [];
        foreach ($sessionEntities as $session) {
            $sessions[] = [
                'user' => $session->getUser(),
                'availability' => $session->getAvailability(),
                'isPresent' => $session->isPresent(),
            ];
        }

        return $sessions;
    }

    public function getAvailableSessions(Collection $sessionEntities): ArrayCollection
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

    public function getUsersOnSiteCount(Collection $sessionEntities, BikeRide $bikeRide): int
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
