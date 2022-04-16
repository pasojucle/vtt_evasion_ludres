<?php

declare(strict_types=1);

namespace App\ViewModel\Indemnity;

use App\Entity\Level;
use App\Model\Currency;
use App\Entity\BikeRide;
use App\Entity\Indemnity;
use App\ViewModel\AbstractViewModel;

class IndemnityViewModel extends AbstractViewModel
{
    public ?array $services;
    
    public ?Indemnity $entity;

    public ?Level $level;

    public ?string $bikeRideType;

    public ?string $amount;

    public static function fromIndemnity(Indemnity $indemnity)
    {
        $indemnityView = new self();
        $indemnityView->entity = $indemnity;
        $indemnityView->level = $indemnity->getLevel();
        $indemnityView->bikeRideType = BikeRide::TYPES[$indemnity->getBikeRideType()];
        $amount = new Currency($indemnity->getAmount());
        $indemnityView->amount = $amount->toString();

        return $indemnityView;
    }
}
