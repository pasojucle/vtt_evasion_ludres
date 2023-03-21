<?php

declare(strict_types=1);

namespace App\ViewModel\BikeRideType;

use App\Entity\BikeRideType;
use App\ViewModel\AbstractViewModel;

class BikeRideTypeViewModel extends AbstractViewModel
{
    public ?BikeRideType $entity;

    public ?string $name;

    public ?string $content;

    public ?bool $isSchool;

    public ?bool $isRegistrable;

    public ?bool $useLevels;

    public ?bool $isShowMemberList;

    public static function fromBikeRideType(BikeRideType $bikeRideType)
    {
        $bikeRideTypeView = new self();
        $bikeRideTypeView->entity = $bikeRideType;
        $bikeRideTypeView->content = $bikeRideType->getContent();
        $bikeRideTypeView->useLevels = $bikeRideType->isUseLevels();
        $bikeRideTypeView->isShowMemberList = $bikeRideType->isShowMemberList();
        $bikeRideTypeView->isSchool = BikeRideType::REGISTRATION_SCHOOL === $bikeRideType->getRegistration();
        $bikeRideTypeView->isRegistrable = BikeRideType::REGISTRATION_NONE !== $bikeRideType->getRegistration();
        ;
        

        return $bikeRideTypeView;
    }
}
