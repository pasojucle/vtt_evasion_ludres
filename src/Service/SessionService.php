<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\Cluster;
use App\Entity\Session;
use App\Service\UserService;
use App\DataTransferObject\User;
use App\Entity\User as UserEntity;
use App\Repository\LevelRepository;
use App\Repository\SessionRepository;
use Doctrine\Common\Collections\Collection;


class SessionService
{
    private SessionRepository $sessionRepository;
    private UserService $userService;
    private LevelRepository $levelRepository;
    
    public function __construct(SessionRepository $sessionRepository, UserService $userService, LevelRepository $levelRepository)
    {
        $this->sessionRepository = $sessionRepository;
        $this->userService = $userService;
        $this->levelRepository = $levelRepository;
    }
    
    public function getSessionsBytype(Event $event, ?UserEntity $user = null): array
    {
        $members = [];
        $framers = [];
        $sessions = $this->sessionRepository->findByEvent($event);
 
        if (null !== $sessions) {
            foreach($sessions as $session) {
                if (null === $session->getAvailability()) {
                    $level = $session->getUser()->getLevel();
                    $levelId = (null !== $level) ? $level->getId() : 0;
                    $levelTitle = (null !== $level) ? $level->getTitle() : 'non renseigné';
                    $members[$levelId]['members'] = $session->getUser();
                    $members[$levelId]['title'] = $levelTitle;
                } else {
                    if ($user !== $session->getUser()) {
                        $framers[] = [
                            'user' => $this->userService->convertToUser($session->getUser()),
                            'availability' => $session->getAvailabilityToView(),
                        ];
                    }
                }
            }
        } 

        return [$framers, $members];
    }

    public function getCluster(Event $event, UserEntity $user, Collection $clusters)
    {
        $userCluster = null;
        if ($event->getType() === Event::TYPE_SCHOOL) {
            $clustersLevelAsUser = [];
            $userLevel = (null !== $user->getLevel()) ? $user->getLevel() : $this->levelRepository->findAwaitingEvaluation();
            foreach($event->getClusters() as $cluster) {
                if (null !== $cluster->getLevel() && $cluster->getLevel() === $userLevel) {
                    $clustersLevelAsUser[] = $cluster;
                    if (count($cluster->getMemberSessions()) <= $cluster->getMaxUsers()) {
                        $userCluster = $cluster;
                    }
                }

                if (null !== $cluster->getRole() && $user->hasRole($cluster->getRole())) {
                    $userCluster = $cluster;
                }
            }

            if (null === $userCluster) {
                $cluster = new Cluster();
                $count = count($clustersLevelAsUser) + 1;
                $cluster->setTitle($userLevel->getTitle().' '.$count)
                    ->setLevel($userLevel)
                    ->setEvent($event)
                    ->setMaxUsers(Cluster::SCHOOL_MAX_MEMEBERS);
            }
        }
        
        if (null === $userCluster && 1 === $clusters->count()) {
            $userCluster = $clusters->first();
        }
        return $userCluster;
    }
}