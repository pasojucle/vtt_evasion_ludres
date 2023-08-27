<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\BikeRideTypeDto;
use App\Dto\SessionDto;
use App\Entity\Session;
use App\Entity\User;
use App\Model\Currency;
use App\Repository\IndemnityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;

class SessionDtoTransformer
{
    public function __construct(
        private BikeRideDtoTransformer $bikeRideDtoTransformer,
        private IndemnityRepository $indemnityRepository,
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
        }

        return $sessionDto;
    }

    public function fromEntities(Paginator|Collection|array $sessionEntities): array
    {
        $sessions = [];
        foreach ($sessionEntities as $sessionEntity) {
            $sessions[] = $this->fromEntity($sessionEntity);
        }

        return $sessions;
    }

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
}
