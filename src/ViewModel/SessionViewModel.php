<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\Session;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class SessionViewModel extends AbstractViewModel
{
    public ?Session $entity;

    public ?array $availability;

    public ?BikeRideViewModel $bikeRide;

    public ?UserViewModel $user;

    public ?bool $userIsOnSite;

    public static function fromSession(Session $session, array $services)
    {
        $sessionView = new self();
        $sessionView->entity = $session;
        $sessionView->availability = $sessionView->getAvailability();
        $sessionView->bikeRide = BikeRideViewModel::fromBikeRide($session->getCluster()->getBikeRide(), $services);
        $sessionView->user = UserViewModel::fromUser($session->getUser(), $services);
        $sessionView->userIsOnSite = $session->isPresent();

        return $sessionView;
    }

    public function getAvailability(): array
    {
        $availbilityColors = [
            1 => 'success',
            2 => 'alert-warning',
            3 => 'alert-danger',
        ];

        $availability = [];
        if (null !== $this->availability) {
            $availability = [
                'class' => $availbilityColors[$this->availability],
                'text' => Session::AVAILABILITIES[$this->availability],
            ];
        }

        return $availability;
    }
}
