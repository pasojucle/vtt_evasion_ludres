<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\BikeRide;
use App\Entity\Cluster;
use App\Entity\Level;
use App\Entity\User;
use App\Form\Admin\LevelType;
use App\Repository\LevelRepository;
use App\Repository\SessionRepository;
use App\ViewModel\ClusterPresenter;
use App\ViewModel\SessionPresenter;
use App\ViewModel\UserPresenter;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;

class SessionService
{
    private array $seasonStartAt;

    public function __construct(
        private SessionRepository $sessionRepository,
        private UserPresenter $userPresenter,
        private LevelRepository $levelRepository,
        private MailerService $mailerService,
        private ClusterPresenter $clusterPresenter,
        private ParameterService $parameterService,
        private ClusterService $clusterService,
        private SessionPresenter $sessionPresenter,
    ) {
        $this->seasonStartAt = $this->parameterService->getParameterByName('SEASON_START_AT');
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
                        $this->sessionPresenter->present($session);
                        $framers[] = $this->sessionPresenter->viewModel();
                    }
                }
            }
        }

        return [$framers, $members];
    }

    public function getCluster(BikeRide $bikeRide, User $user, Collection $clusters): Cluster
    {
        $userCluster = null;
        if ($bikeRide->getBikeRideType()->isSchool()) {
            $clustersLevelAsUser = [];
            foreach ($bikeRide->getClusters() as $cluster) {
                if (null !== $cluster->getLevel() && $cluster->getLevel() === $user->getLevel()) {
                    $clustersLevelAsUser[] = $cluster;
                    if (count($this->clusterService->getMemberSessions($cluster)) <= $cluster->getMaxUsers()) {
                        $userCluster = $cluster;
                    }
                }

                if (null !== $cluster->getRole() && ($user->hasRole($cluster->getRole()) || Level::TYPE_ADULT_MEMBER === $user->getLevel()->getType())) {
                    $userCluster = $cluster;
                }
            }

            if (null === $userCluster) {
                $cluster = new Cluster();
                $count = count($clustersLevelAsUser) + 1;
                $cluster->setTitle($user->getLevel()->getTitle() . ' ' . $count)
                    ->setLevel($$user->getLevel())
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
                'name' => $this->userPresenter->viewModel()->member->name,
                'firstName' => $this->userPresenter->viewModel()->member->firstName,
                'email' => $this->userPresenter->viewModel()->mainEmail,
                'subject' => 'Fin de la période d\'essai',
                'testing_end' => true,
            ], 'EMAIL_END_TESTING');
        }
    }

    public function getSeasonInterval(int $season): array
    {
        $startAt = DateTimeImmutable::createFromFormat('Y-m-d', implode('-', [$season - 1, $this->seasonStartAt['month'], $this->seasonStartAt['day']]));
        $endAt = DateTimeImmutable::createFromFormat('Y-m-d', implode('-', [$season, $this->seasonStartAt['month'], $this->seasonStartAt['day']]));
        $endAt->sub(new DateInterval('PT1D'));

        $interval = [
            'startAt' => $startAt->setTime(0, 0, 0, ),
            'endAt' => $endAt->setTime(0, 0, 0, ),
        ];

        return $interval;
    }
}
