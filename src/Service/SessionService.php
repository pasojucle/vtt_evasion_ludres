<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Entity\Cluster;
use App\Entity\BikeRide;
use App\ViewModel\UserPresenter;
use App\Repository\LevelRepository;
use App\ViewModel\ClusterPresenter;
use App\Repository\SessionRepository;
use Doctrine\Common\Collections\Collection;

class SessionService
{
    public function __construct(
        private SessionRepository $sessionRepository,
        private UserPresenter $userPresenter,
        private LevelRepository $levelRepository,
        private MailerService $mailerService,
        private ClusterPresenter $clusterPresenter
    ) {
    }

    public function getSessionsBytype(BikeRide $bikeRide, ?User $user = null): array
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
                        $this->userPresenter->present($session->getUser());
                        $framers[] = [
                            'user' => $this->userPresenter->viewModel(),
                            'availability' => $session->getAvailabilityToView(),
                        ];
                    }
                }
            }
        }

        return [$framers, $members];
    }

    public function getCluster(BikeRide $bikeRide, User $user, Collection $clusters)
    {
        $userCluster = null;
        if (BikeRide::TYPE_SCHOOL === $bikeRide->getType()) {
            $clustersLevelAsUser = [];
            $userLevel = (null !== $user->getLevel()) ? $user->getLevel() : $this->levelRepository->findAwaitingEvaluation();
            foreach ($bikeRide->getClusters() as $cluster) {
                $this->clusterPresenter->present($cluster);
                $cluster = $this->clusterPresenter->viewModel();
                if (null !== $cluster->level && $cluster->level === $userLevel) {
                    $clustersLevelAsUser[] = $cluster;
                    if (count($cluster->memberSessions) <= $cluster->maxUsers) {
                        $userCluster = $cluster;
                    }
                }

                if (null !== $cluster->role && $user->hasRole($cluster->role)) {
                    $userCluster = $cluster;
                }
            }

            if (null === $userCluster) {
                $cluster = new Cluster();
                $count = count($clustersLevelAsUser) + 1;
                $cluster->setTitle($userLevel->getTitle().' '.$count)
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

    public function checkEndTesting(User $user): void
    {
        $this->userPresenter->present($user);

        if ($this->userPresenter->viewModel()->isEndTesting()) {
            $this->mailerService->sendMailToMember([
                'name' => $this->userPresenter->viewModel()->member['name'],
                'firstName' => $this->userPresenter->viewModel()->member['firstName'],
                'email' => $$this->userPresenter->viewModel()->getContactEmail(),
                'subject' => 'Fin de la période d\'essai',
                'testing_end' => true,
            ], 'EMAIL_END_TESTING');
        }
    }
}
