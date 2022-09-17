<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\Session;
use App\Model\Currency;
use App\Service\IndemnityService;

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

    private IndemnityService $indemnityService;

    public static function fromSession(Session $session, ServicesPresenter $services)
    {
        $sessionView = new self();
        $sessionView->entity = $session;
        $sessionView->availability = $sessionView->getAvailability();
        $sessionView->bikeRide = BikeRideViewModel::fromBikeRide($session->getCluster()->getBikeRide(), $services);
        $sessionView->user = UserViewModel::fromUser($session->getUser(), $services);
        $sessionView->userIsOnSite = $session->isPresent();
        $sessionView->allIndemnities = $services->allIndemnities;
        $sessionView->indemnityService = $services->indemnityService;
        $sessionView->indemnity = $sessionView->getIndemnity();
        $sessionView->indemnityStr = ($sessionView->getIndemnity()) ? $sessionView->getIndemnity()->toString() : null;

        return $sessionView;
    }

    private function getAvailability(): array
    {
        $availbilityClass = [
            1 => ['badge' => 'person person-check', 'icon' => '<i class="fa-solid fa-person-circle-check"></i>', 'color' => 'success-color'],
            2 => ['badge' => 'person person-question', 'icon' => '<i class="fa-solid fa-person-circle-question"></i>', 'color' => 'warning-color'],
            3 => ['badge' => 'person person-xmark', 'icon' => '<i class="fa-solid fa-person-circle-xmark"></i>', 'color' => 'alert-danger-color'],
        ];
        $availability = [];
        if (null !== $this->entity->getAvailability()) {
            $availability = [
                'class' => $availbilityClass[$this->entity->getAvailability()],
                'text' => Session::AVAILABILITIES[$this->entity->getAvailability()],
                'value' => $this->entity->getAvailability(),
            ];
        }

        return $availability;
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
}
