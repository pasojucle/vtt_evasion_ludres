<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\BikeRideTypeDto;
use App\Dto\SessionDto;
use App\Entity\BikeRideType;
use App\Entity\Session;
use App\Entity\User;
use App\Model\Currency;
use App\Repository\IndemnityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Contracts\Translation\TranslatorInterface;

class SessionDtoTransformer
{
    public function __construct(
        private BikeRideDtoTransformer $bikeRideDtoTransformer,
        private IndemnityRepository $indemnityRepository,
        private UserDtoTransformer $userDtoTransformer,
        private TranslatorInterface $translator,
    ) {
    }


    public function fromEntity(?Session $session): SessionDto
    {
        $sessionDto = new SessionDto();
        if ($session) {
            $sessionDto->id = $session->getId();
            $sessionDto->availability = $this->getAvailability($session->getAvailability());
            $sessionDto->bikeRide = $this->bikeRideDtoTransformer->getHeaderFromEntity($session->getCluster()->getBikeRide());
            $sessionDto->user = $this->userDtoTransformer->fromEntity($session->getUser());
            $sessionDto->userIsOnSite = $session->isPresent();
            $sessionDto->userIsOnSiteToStr = $this->getUserIsOnSiteToStr($session->isPresent());
            $sessionDto->userIsOnSiteToHtml = $this->getUserIsOnSiteToHtml($session->isPresent());
            $sessionDto->indemnity = $this->getIndemnity($session->getUser(), $session->getCluster()->getBikeRide()->getBikeRideType(), $sessionDto->userIsOnSite);
            $sessionDto->indemnityStr = ($sessionDto->indemnity) ? $sessionDto->indemnity->toString() : null;
            $sessionDto->cluster = $session->getCluster()->getTitle();
            $sessionDto->bikeKind = ($session->getBikeKind()) ? $this->translator->trans(Session::BIKEKINDS[$session->getBikeKind()]) : null;
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

    private function getIndemnity(User $user, BikeRideType $bikeRideType, bool $userIsOnSite): ?Currency
    {
        foreach ($this->indemnityRepository->findAll() as $indemnity) {
            if ($bikeRideType === $indemnity->getBikeRideType() && $user->getLevel() === $indemnity->getLevel() && $userIsOnSite) {
                $amount = new Currency($indemnity->getAmount());

                return $amount;
            }
        }

        return null;
    }

    private function getUserIsOnSiteToStr(bool $isPresent): string
    {
        return ($isPresent) ? 'Présent' : 'Absent';
    }

    private function getUserIsOnSiteToHtml(bool $isPresent): string
    {
        return ($isPresent) ? '<span class="success"></span> Présent' : '<span class="alert-danger"></span> Absent';
    }
}
