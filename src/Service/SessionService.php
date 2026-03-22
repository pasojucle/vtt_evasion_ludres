<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\BikeRide;
use App\Entity\Cluster;
use App\Entity\Enum\AvailabilityEnum;
use App\Entity\Enum\BikeTypeEnum;
use App\Entity\Enum\PracticeEnum;
use App\Entity\Enum\RegistrationEnum;
use App\Entity\Level;
use App\Entity\Member;
use App\Repository\SessionRepository;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Symfony\Contracts\Translation\TranslatorInterface;

class SessionService
{
    private array $seasonStartAt;

    public function __construct(
        private SessionRepository $sessionRepository,
        private UserDtoTransformer $userDtoTransformer,
        private MailerService $mailerService,
        private ParameterService $parameterService,
        private MessageService $messageService,
        private ClusterService $clusterService,
        private TranslatorInterface $translator,
    ) {
        $this->seasonStartAt = $this->parameterService->getParameterByName('SEASON_START_AT');
    }

    public function getSessionsBytype(BikeRide $bikeRide, ?Member $member = null): array
    {
        $members = [];
        $framers = [];
        $sessions = $this->sessionRepository->findByBikeRide($bikeRide);

        foreach ($sessions as $session) {
            if (AvailabilityEnum::NONE === $session->getAvailability()) {
                $level = $session->getUser()->getLevel();
                $levelId = (null !== $level) ? $level->getId() : 0;
                $levelTitle = (null !== $level) ? $level->getTitle() : 'non renseigné';
                $members[$levelId]['members'][] = $session->getUser();
                $members[$levelId]['title'] = $levelTitle;
            } else {
                if ($member !== $session->getUser()) {
                    // $framers[] = $this->sessionDtoTransformer->fromEntity($session);
                }
            }
        }

        return ['framers' => $framers, 'members' => $members];
    }
    
    public function getCluster(BikeRide $bikeRide, Member $member, Collection $clusters): ?Cluster
    {
        $userCluster = null;

        if ($bikeRide->getBikeRideType()->isNeedFramers() && Level::TYPE_SCHOOL_MEMBER !== $member->getLevel()->getType()) {
            foreach ($bikeRide->getClusters() as $cluster) {
                if ('ROLE_FRAME' === $cluster->getRole()) {
                    return $cluster;
                }
            }
        }

        if (RegistrationEnum::CLUSTERS === $bikeRide->getBikeRideType()->getRegistration() && 1 < $this->selectableClusterCount($bikeRide, $clusters)) {
            return $userCluster;
        }

        if (RegistrationEnum::SCHOOL === $bikeRide->getBikeRideType()->getRegistration()) {
            $clustersLevelAsUser = [];
            foreach ($bikeRide->getClusters() as $cluster) {
                if (null !== $cluster->getLevel() && $cluster->getLevel() === $member->getLevel()) {
                    $clustersLevelAsUser[] = $cluster;
                    if (count($this->clusterService->getMemberSessions($cluster)) <= $cluster->getMaxUsers()) {
                        $userCluster = $cluster;
                    }
                }
            }

            if (null === $userCluster) {
                $cluster = new Cluster();
                $count = count($clustersLevelAsUser) + 1;
                $cluster->setTitle($member->getLevel()->getTitle() . ' ' . $count)
                    ->setLevel($member->getLevel())
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

    public function checkEndTesting(Member $member): void
    {
        $userDto = $this->userDtoTransformer->identifiersFromEntity($member);
        if ($userDto->isEndTesting) {
            $subject = 'Fin de la période d\'essai';
            $this->mailerService->sendMailToMember($userDto, $subject, $this->messageService->getMessageByName('EMAIL_END_TESTING'));
        }
    }

    public function getSeasonPeriod(int $season): array
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

    public function getAvailability(AvailabilityEnum $availability): array
    {
        $availbilityClass = [
            AvailabilityEnum::REGISTERED->name => ['badge' => 'person person-check', 'icon' => '<i class="fa-solid fa-person-circle-check"></i>', 'color' => 'success-color'],
            AvailabilityEnum::AVAILABLE->name => ['badge' => 'person person-question', 'icon' => '<i class="fa-solid fa-person-circle-question"></i>', 'color' => 'warning-color'],
            AvailabilityEnum::UNAVAILABLE->name => ['badge' => 'person person-xmark', 'icon' => '<i class="fa-solid fa-person-circle-xmark"></i>', 'color' => 'alert-danger-color'],
            AvailabilityEnum::NONE->name => ['badge' => 'person person-xmark', 'icon' => '<i class="fa-solid fa-person-circle-xmark"></i>', 'color' => 'alert-danger-color'],
        ];

        $availabilityView = [
            'class' => $availbilityClass[$availability->name],
            'text' => $availability->trans($this->translator),
            'value' => $availability->name,
            'enum' => $availability,
        ];

        return $availabilityView;
    }
}
