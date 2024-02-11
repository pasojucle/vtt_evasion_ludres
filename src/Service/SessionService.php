<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\DtoTransformer\SessionDtoTransformer;
use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\BikeRide;
use App\Entity\BikeRideType;
use App\Entity\Cluster;
use App\Entity\Level;
use App\Entity\User;
use App\Repository\SessionRepository;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;

class SessionService
{
    private array $seasonStartAt;

    public function __construct(
        private SessionRepository $sessionRepository,
        private UserDtoTransformer $userDtoTransformer,
        private MailerService $mailerService,
        private ParameterService $parameterService,
        private ClusterService $clusterService,
        private SessionDtoTransformer $sessionDtoTransformer,
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
                    $framers[] = $this->sessionDtoTransformer->fromEntity($session);
                }
            }
        }

        return ['framers' => $framers, 'members' => $members];
    }

    public function getBikeRideMembers(BikeRide $bikeRide): array
    {
        $sessionEntities = $this->sessionRepository->findByBikeRide($bikeRide);

        $sessionsByCluster = [];
        $bikeRides = [];
        foreach ($sessionEntities as $sessionEntity) {
            $sessionDto = $this->sessionDtoTransformer->fromEntity($sessionEntity);
            $sessions[] = $sessionDto;
            $cluster = $sessionEntity->getCluster();
            $sessionsByCluster[$cluster->getId()][] = $sessionDto;
            $bikeRide = $cluster->getBikeRide();
            $bikeRides[$bikeRide->getId()] = $bikeRide;
        }

        $maxCount = 0;
        $clusters = [];
        $header = [];
        $rows = [];

        foreach ($bikeRides as $bikeRide) {
            foreach ($bikeRide->getClusters() as $cluster) {
                $header[] = $cluster->getTitle();
                $clusters[] = $cluster->getId();
            }
        }
        
        foreach ($sessionsByCluster as $sessions) {
            if ($maxCount < count($sessions)) {
                $maxCount = count($sessions);
            }
        }

        foreach ($clusters as $cluster) {
            for ($i = 0; $i < $maxCount; ++$i) {
                $session = (array_key_exists($cluster, $sessionsByCluster) && array_key_exists($i, $sessionsByCluster[$cluster]))
                    ? sprintf('%s <span class="badge badge-info small">%s</span>', $sessionsByCluster[$cluster][$i]->user->member->fullName, $sessionsByCluster[$cluster][$i]->bikeKind)
                    : '';
                $rows[$i][] = $session;
            }
        }
        return ['header' => $header, 'rows' => $rows];
    }
    
    public function getCluster(BikeRide $bikeRide, User $user, Collection $clusters): ?Cluster
    {
        $userCluster = null;

        if ($bikeRide->getBikeRideType()->isNeedFramers() && Level::TYPE_SCHOOL_MEMBER !== $user->getLevel()->getType()) {
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
                    ->setMaxUsers(Cluster::SCHOOL_MAX_MEMBERS)
                ;
            }
        }

        if (null === $userCluster) {
            foreach ($bikeRide->getClusters() as $cluster) {
                if ('ROLE_FRAME' !== $cluster->getRole()) {
                    $userCluster = $cluster;
                    continue;
                }
            }
        }

        if (null === $userCluster && 0 < $clusters->count()) {
            $userCluster = $clusters->first();
        }
        
        return $userCluster;
    }

    public function checkEndTesting(User $user): void
    {
        $userDto = $this->userDtoTransformer->fromEntity($user);

        if ($userDto->isEndTesting) {
            $subject = 'Fin de la période d\'essai';
            $this->mailerService->sendMailToMember($userDto, $subject, $this->parameterService->getParameterByName('EMAIL_END_TESTING'));
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

    public function selectableClusterCount(BikeRide $bikeRide, Collection $clusters): int
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
