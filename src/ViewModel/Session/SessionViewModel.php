<?php

declare(strict_types=1);

namespace App\ViewModel\Session;

use App\Entity\Session;
use App\Model\Currency;
use App\ViewModel\AbstractViewModel;
use App\ViewModel\BikeRideViewModel;
use App\ViewModel\ServicesPresenter;
use App\ViewModel\UserViewModel;

class SessionViewModel extends AbstractViewModel
{
    public ?Session $entity;

    public ?array $availability;

    public ?BikeRideViewModel $bikeRide;

    public ?UserViewModel $user;

    public ?bool $userIsOnSite;

    public ?Currency $indemnity;

    public ?string $indemnityStr;

    private array $allIndemnities;

    private ServicesPresenter $services;

    public static function fromSession(Session $session, ServicesPresenter $services)
    {
        $sessionView = new self();
        $sessionView->entity = $session;
        $sessionView->services = $services;
        $sessionView->availability = $sessionView->getAvailability();
        $sessionView->bikeRide = BikeRideViewModel::fromBikeRide($session->getCluster()->getBikeRide(), $services);
        $sessionView->user = UserViewModel::fromUser($session->getUser(), $services);
        $sessionView->userIsOnSite = $session->isPresent();
        $sessionView->allIndemnities = $services->allIndemnities;
        $sessionView->indemnity = $sessionView->getIndemnity();
        $sessionView->indemnityStr = ($sessionView->getIndemnity()) ? $sessionView->getIndemnity()->toString() : null;

        return $sessionView;
    }

    private function getAvailability(): array
    {
        $availability = $this->entity->getAvailability();

        $availbilityClass = [
            1 => ['badge' => 'person person-check', 'icon' => '<i class="fa-solid fa-person-circle-check"></i>', 'color' => 'success-color'],
            2 => ['badge' => 'person person-question', 'icon' => '<i class="fa-solid fa-person-circle-question"></i>', 'color' => 'warning-color'],
            3 => ['badge' => 'person person-xmark', 'icon' => '<i class="fa-solid fa-person-circle-xmark"></i>', 'color' => 'alert-danger-color'],
        ];

        $availabilityView = [];
        if (null !== $this->entity->getAvailability()) {
            $availabilityView = [
                'class' => $availbilityClass[$availability],
                'text' => Session::AVAILABILITIES[$availability],
                'value' => $availability,
            ];
        }

        return $availabilityView;
    }

    private function getIndemnity(): ?Currency
    {
        if (!empty($this->allIndemnities)) {
            foreach ($this->allIndemnities as $indemnity) {
                if ($this->bikeRide->bikeRideType === $indemnity->getBikeRideType() && $this->user->entity->getLevel() === $indemnity->getLevel() && $this->userIsOnSite) {
                    $amount = new Currency($indemnity->getAmount());

                    return $amount;
                }
            }
        }

        return null;
    }

    public function getBikeRideMemberList(): ?array
    {
        if ($this->bikeRide->bikeRideType->isShowMemberList()) {
            $sessions = $this->services->sessionRepository->findByBikeRide($this->bikeRide->entity);
            return SessionsViewModel::fromSessions($sessions, $this->services)->sessions;
        }

        return null;
    }
}
