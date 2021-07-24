<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\Session;
use App\Entity\User as UserEntity;
use App\DataTransferObject\User;
use App\Repository\SessionRepository;


class SessionService
{
    private SessionRepository $sessionRepository;
    
    public function __construct(SessionRepository $sessionRepository)
    {
        $this->sessionRepository = $sessionRepository;
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
                            'user' => new User($session->getUser()),
                            'availability' => $session->getAvailabilityToView(),
                        ];
                    }
                }
            }
        } 

        return [$framers, $members];
    }
}