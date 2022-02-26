<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\BikeRide;
use App\Entity\Cluster;
use App\Entity\User as UserEntity;
use App\Repository\LevelRepository;
use App\Repository\SessionRepository;
use Doctrine\Common\Collections\Collection;

class SessionService
{
    private SessionRepository $sessionRepository;

    private UserService $userService;

    private LevelRepository $levelRepository;

    private MailerService $mailerService;

    public function __construct(
        SessionRepository $sessionRepository,
        UserService $userService,
        LevelRepository $levelRepository,
        MailerService $mailerService
    ) {
        $this->sessionRepository = $sessionRepository;
        $this->userService = $userService;
        $this->levelRepository = $levelRepository;
        $this->mailerService = $mailerService;
    }

    public function getSessionsBytype(BikeRide $bikeRide, ?UserEntity $user = null): array
    {
        $members = [];
        $framers = [];
        $sessions = $this->sessionRepository->findByBikeRide($bikeRide);

        if (null !== $sessions) {
            foreach ($sessions as $session) {
                if (null === $session->getAvailability()) {
                    $level = $session->getUser()->getLevel();
                    $levelId = (null !== $level) ? $level->getId() : 0;
                    $levelTitle = (null !== $level) ? $level->getTitle() : 'non renseigné';
                    $members[$levelId]['members'][] = $session->getUser();
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

    public function getCluster(BikeRide $bikeRide, UserEntity $user, Collection $clusters)
    {
        $userCluster = null;
        if (BikeRide::TYPE_SCHOOL === $bikeRide->getType()) {
            $clustersLevelAsUser = [];
            $userLevel = (null !== $user->getLevel()) ? $user->getLevel() : $this->levelRepository->findAwaitingEvaluation();
            foreach ($bikeRide->getClusters() as $cluster) {
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
                $cluster->setTitle($userLevel->getTitle() . ' ' . $count)
                    ->setLevel($userLevel)
                    ->setBikeRide($bikeRide)
                    ->setMaxUsers(Cluster::SCHOOL_MAX_MEMEBERS)
                ;
            }
        }

        if (null === $userCluster && 1 === $clusters->count()) {
            $userCluster = $clusters->first();
        }

        return $userCluster;
    }

    public function checkEndTesting(UserEntity $entityUser): void
    {
        $user = $this->userService->convertToUser($entityUser);

        if ($user->isEndTesting()) {
            $this->mailerService->sendMailToMember([
                'name' => $user->getMember()['name'],
                'firstName' => $user->getMember()['firstName'],
                'email' => $user->getContactEmail(),
                'subject' => 'Fin de la période d\'essai',
                'testing_end' => true,
            ], 'EMAIL_END_TESTING');
        }
    }
}
