<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\BikeRideDto;
use App\Dto\BikeRideTypeDto;
use App\Dto\SessionDto;
use App\Dto\SessionsDto;
use App\Entity\Session;
use App\Entity\User;
use App\Model\Currency;
use App\Repository\IndemnityRepository;
use App\Repository\SessionRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;

class SessionDtoTransformer
{
    // private array $sessions;
    // private array $sessionsByCluster;
    // private array $bikeRides;

    public function __construct(
        private BikeRideDtoTransformer $bikeRideDtoTransformer,
        private IndemnityRepository $indemnityRepository,
        private SessionRepository $sessionRepository,
        private UserDtoTransformer $userDtoTransformer
    ) {
    }


    public function fromEntity(?Session $session): SessionDto
    {
        $sessionDto = new SessionDto();
        if ($session) {
            $sessionDto->id = $session->getId();
            $sessionDto->availability = $this->getAvailability($session->getAvailability());
            $sessionDto->bikeRide = $this->bikeRideDtoTransformer->fromEntity($session->getCluster()->getBikeRide());
            $sessionDto->user = $this->userDtoTransformer->fromEntity($session->getUser());
            $sessionDto->userIsOnSite = $session->isPresent();
            $sessionDto->indemnity = $this->getIndemnity($session->getUser(), $sessionDto->bikeRide->bikeRideType, $sessionDto->userIsOnSite);
            $sessionDto->indemnityStr = ($sessionDto->indemnity) ? $sessionDto->indemnity->toString() : null;
            $sessionDto->cluster = $session->getCluster()->getTitle();
            // $sessionDto->bikeRideMemeberList = $this->getBikeRideMemberList($sessionDto->bikeRide);
        }

        return $sessionDto;
    }


    public function fromEntities(Paginator|Collection|array $sessionEntities): array
    {
        foreach ($sessionEntities as $sessionEntity) {
            $sessions[] = $this->fromEntity($sessionEntity);
        }

        return $sessions;
    }


    // public function fromEntities(Paginator|Collection|array $sessionEntities): SessionsDto
    // {
    //     $sessionsDto = new SessionsDto();
    //     list($sessions, $sessionsByCluster, $bikeRides) = $this->analize($sessionEntities);
    //     $sessionsDto->sessions = $sessions;
    //     $sessionsDto->bikeRideMembers = $this->getBikeRideMembers($bikeRides, $sessionsByCluster);

    //     return $sessionsDto;
    // }

    private function getAvailability(?int $availability): array
    {
        $availbilityClass = [
            1 => ['badge' => 'person person-check', 'icon' => '<i class="fa-solid fa-person-circle-check"></i>', 'color' => 'success-color'],
            2 => ['badge' => 'person person-question', 'icon' => '<i class="fa-solid fa-person-circle-question"></i>', 'color' => 'warning-color'],
            3 => ['badge' => 'person person-xmark', 'icon' => '<i class="fa-solid fa-person-circle-xmark"></i>', 'color' => 'alert-danger-color'],
        ];

        $availabilityView = [];
        if (null !== $availability) {
            $availabilityView = [
                'class' => $availbilityClass[$availability],
                'text' => Session::AVAILABILITIES[$availability],
                'value' => $availability,
            ];
        }

        return $availabilityView;
    }

    private function getIndemnity(User $user, BikeRideTypeDto $bikeRideType, bool $userIsOnSite): ?Currency
    {
        foreach ($this->indemnityRepository->findAll() as $indemnity) {
            if ($bikeRideType === $indemnity->getBikeRideType() && $user->getLevel() === $indemnity->getLevel() && $userIsOnSite) {
                $amount = new Currency($indemnity->getAmount());

                return $amount;
            }
        }

        return null;
    }

    // public function getBikeRideMemberList(BikeRideDto $bikeRide): ?array
    // {
    //     if ($bikeRide->bikeRideType->isShowMemberList) {
    //         $sessions = $this->sessionRepository->findByBikeRideId($bikeRide->id);
    //         return $this->fromEntities($sessions)->bikeRideMembers;
    //     }

    //     return null;
    // }

    public function analize(Collection|array $sessionEntities): array
    {
        $sessions = [];
        $sessionsByCluster = [];
        $bikeRides = [];
        foreach ($sessionEntities as $sessionEntity) {
            $sessionDto = $this->fromEntity($sessionEntity);
            $sessions[] = $sessionDto;
            $cluster = $sessionEntity->getCluster();
            $sessionsByCluster[$cluster->getId()][] = $sessionDto;
            $bikeRide = $cluster->getBikeRide();
            $bikeRides[$bikeRide->getId()] = $bikeRide;
        }

        return [$sessions, $sessionsByCluster, $bikeRides];
    }

    public function getBikeRideMembers(array $bikeRides, array $sessionsByCluster): array
    {
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
                    ? $sessionsByCluster[$cluster][$i]->user->member->fullName
                    : '';
                $rows[$i][] = $session;
            }
        }
        return ['header' => $header, 'rows' => $rows];
    }
}
