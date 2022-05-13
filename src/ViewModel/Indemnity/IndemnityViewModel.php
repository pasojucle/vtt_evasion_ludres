<?php

declare(strict_types=1);

namespace App\ViewModel\Indemnity;

use App\Entity\BikeRideType;
use App\Entity\Indemnity;
use App\Entity\Level;
use App\Model\Currency;
use App\ViewModel\AbstractViewModel;

class IndemnityViewModel extends AbstractViewModel
{
    public ?array $services;

    public ?Indemnity $entity;

    public ?Level $level;

    public ?BikeRideType $bikeRideType;

    public ?string $amount;

    public static function fromIndemnity(Indemnity $indemnity)
    {
        $indemnityView = new self();
        $indemnityView->entity = $indemnity;
        $indemnityView->level = $indemnity->getLevel();
        $indemnityView->bikeRideType = $indemnity->getBikeRideType();
        $amount = new Currency($indemnity->getAmount());
        $indemnityView->amount = $amount->toString();

        return $indemnityView;
    }
}
