<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\SessionDto;
use App\Entity\BikeRideType;
use App\Entity\Enum\AvailabilityEnum;
use App\Entity\Enum\BikeTypeEnum;
use App\Entity\Enum\PracticeEnum;
use App\Entity\Session;
use App\Entity\Member;
use App\Model\Currency;
use App\Repository\IndemnityRepository;
use App\Service\SessionService;
use DateTime;
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
        private SessionService $sessionService,
    ) {
    }

    public function fromEntity(?Session $session): SessionDto
    {
        $sessionDto = new SessionDto();
        if ($session) {
            $sessionDto->id = $session->getId();
            $sessionDto->availability = $this->sessionService->getAvailability($session->getAvailability());
            $sessionDto->bikeRide = $this->bikeRideDtoTransformer->getHeaderFromEntity(bikeRide: $session->getCluster()->getBikeRide(), availability: $session->getAvailability());
            $sessionDto->user = $this->userDtoTransformer->fromEntity($session->getMember());
            $sessionDto->userIsOnSite = $session->isPresent();
            $sessionDto->userIsOnSiteToStr = $this->getUserIsOnSiteToStr($session->isPresent());
            $sessionDto->userIsOnSiteToHtml = $this->getUserIsOnSiteToHtml($session);
            $sessionDto->indemnity = $this->getIndemnity($session->getMember(), $session->getCluster()->getBikeRide()->getBikeRideType(), $sessionDto->userIsOnSite);
            $sessionDto->indemnityStr = ($sessionDto->indemnity) ? $sessionDto->indemnity->toString() : null;
            $sessionDto->cluster = $session->getCluster()->getTitle();
            $sessionDto->practice = $session->getPractice()->toBadge($this->translator);
            $sessionDto->bikeType = $session->getBikeType()->toBadge($this->translator);
        }

        return $sessionDto;
    }

    public function getPresence(?Session $session): SessionDto
    {
        $sessionDto = new SessionDto();
        if ($session) {
            $sessionDto->id = $session->getId();
            $sessionDto->availability = $this->sessionService->getAvailability($session->getAvailability());
            $sessionDto->userIsOnSite = $session->isPresent();
            $sessionDto->userIsOnSiteToStr = $this->getUserIsOnSiteToStr($session->isPresent());
            $sessionDto->userIsOnSiteToHtml = $this->getUserIsOnSiteToIcon($session);
            $sessionDto->practice = $session->getPractice()->toBadge($this->translator);
            $sessionDto->bikeType = $session->getBikeType()->toBadge($this->translator);
        }

        return $sessionDto;
    }

    public function evalFromEntity(Session $session): SessionDto
    {
        $sessionDto = new SessionDto();
        $sessionDto->id = $session->getId();
        $member = $session->getMember()->getIdentity();
        $sessionDto->user = [
            'id' => $session->getMember()->getId(), 
            'fullName' => sprintf('%s %s', $member->getname(), $member->getFirstName())
        ];

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

    private function getIndemnity(Member $member, BikeRideType $bikeRideType, bool $userIsOnSite): ?Currency
    {
        foreach ($this->indemnityRepository->findAll() as $indemnity) {
            if ($bikeRideType === $indemnity->getBikeRideType() && $member->getLevel() === $indemnity->getLevel() && $userIsOnSite) {
                $amount = new Currency($indemnity->getAmount());

                return $amount;
            }
        }

        return null;
    }

    private function getUserIsOnSiteToStr(bool $isPresent): string
    {
        return ($isPresent) ? 'Présent' : '-';
    }

    private function getUserIsOnSiteToHtml(Session $session): string
    {
        if ($session->isPresent()) {
            return sprintf('<span class="success"></span> %s', $this->getBadge($session->getPractice()));
        }

        if (new DateTime() < $session->getCluster()->getBikeRide()->getStartAt() && AvailabilityEnum::UNAVAILABLE !== $session->getAvailability()) {
            return '<i class="fas fa-clock warning-color"></i></span> Inscrit';
        }
        
        return '<span class="alert-danger"></span> Absent';
    }

    private function getUserIsOnSiteToIcon(Session $session): string
    {
        if (!$session->isPresent()) {
            return '<i class="fa-solid fa-user-xmark alert-danger"></i>';
        }

        return $this->getBadge($session->getPractice());
    }

    private function getBadge(PracticeEnum $practice): string
    {
        return sprintf('<span class="bs-badge" style="background-color:%s">%s</span>', $practice->color(), $practice->trans($this->translator));
    }
}
