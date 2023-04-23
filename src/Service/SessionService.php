<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\BikeRide;
use App\Entity\BikeRideType;
use App\Entity\Cluster;
use App\Entity\Level;
use App\Entity\User;
use App\Repository\SessionRepository;
use App\ViewModel\Session\SessionPresenter;
use App\ViewModel\Session\SessionsPresenter;
use App\ViewModel\UserPresenter;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Symfony\Bundle\SecurityBundle\Security;

class SessionService
{
    private array $seasonStartAt;

    public function __construct(
        private SessionRepository $sessionRepository,
        private UserPresenter $userPresenter,
        private MailerService $mailerService,
        private ParameterService $parameterService,
        private ClusterService $clusterService,
        private SessionPresenter $sessionPresenter,
        private SessionsPresenter $sessionsPresenter,
        private Security $security
    ) {
        $this->seasonStartAt = $this->parameterService->getParameterByName('SEASON_START_AT');
    }

    public function getSessionsBytype(BikeRide $bikeRide, ?User $user = null): array
    {
        $members = [];
        $framers = [];
        $sessions = $this->sessionRepository->findByBikeRide($bikeRide);

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

        return ['framers' => $framers, 'members' => $members];
    }



    public function getSessions(BikeRide $bikeRide): array
    {
        $sessions = $this->sessionRepository->findByBikeRide($bikeRide);
        $this->sessionsPresenter->present($sessions);
        return $this->sessionsPresenter->viewModel()->bikeRideMembers();
    }

    public function getCluster(BikeRide $bikeRide, User $user, Collection $clusters): ?Cluster
    {
        $userCluster = null;
        if ($bikeRide->getBikeRideType()->isNeedFramers() && ($this->security->isGranted('ROLE_FRAME', $user) || Level::TYPE_ADULT_MEMBER === $user->getLevel()->getType())) {
            foreach ($bikeRide->getClusters() as $cluster) {
                if ('ROLE_FRAME' === $cluster->getRole()) {
                    return $cluster;
                }
            }
        }

        if (BikeRideType::REGISTRATION_CLUSTERS === $bikeRide->getBikeRideType()->getRegistration() && 1 < $this->selectableClusterCount($bikeRide, $clusters)) {
            return $userCluster;
        }

        if (BikeRideType::REGISTRATION_SCHOOL === $bikeRide->getBikeRideType()->getRegistration()) {
            $clustersLevelAsUser = [];
            foreach ($bikeRide->getClusters() as $cluster) {
                if (null !== $cluster->getLevel() && $cluster->getLevel() === $user->getLevel()) {
                    $clustersLevelAsUser[] = $cluster;
                    if (count($this->clusterService->getMemberSessions($cluster)) <= $cluster->getMaxUsers()) {
                        $userCluster = $cluster;
                    }
                }
            }

            if (null === $userCluster) {
                $cluster = new Cluster();
                $count = count($clustersLevelAsUser) + 1;
                $cluster->setTitle($user->getLevel()->getTitle() . ' ' . $count)
                    ->setLevel($user->getLevel())
                    ->setBikeRide($bikeRide)
                    ->setMaxUsers(Cluster::SCHOOL_MAX_MEMEBERS)
                ;
            }
        }

        if (null === $userCluster && 0 < $clusters->count()) {
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

        $interval = [
            'startAt' => $startAt->setTime(0, 0, 0),
            'endAt' => $endAt->sub(new DateInterval('PT1D'))->setTime(0, 0, 0),
        ];

        return $interval;
    }

    private function selectableClusterCount(BikeRide $bikeRide, Collection $clusters): int
    {
        if (!$bikeRide->getBikeRideType()->isNeedFramers()) {
            return $clusters->count();
        }
        $count = 0;
        foreach ($clusters as $cluster) {
            if ('ROLE_FRAME' !== $cluster->getRole()) {
                ++$count;
            }
        }
        return $count;
    }
}
