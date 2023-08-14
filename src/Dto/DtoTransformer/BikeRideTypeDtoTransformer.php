<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\BikeRideTypeDto;
use App\Entity\BikeRideType;

class BikeRideTypeDtoTransformer
{
    public static function fromEntity(BikeRideType $bikeRideType): BikeRideTypeDto
    {
        $bikeRideTypeDto = new BikeRideTypeDto();
        $bikeRideTypeDto->entity = $bikeRideType;
        $bikeRideTypeDto->content = $bikeRideType->getContent();
        $bikeRideTypeDto->useLevels = $bikeRideType->isUseLevels();
        $bikeRideTypeDto->isShowMemberList = $bikeRideType->isShowMemberList();
        $bikeRideTypeDto->isSchool = BikeRideType::REGISTRATION_SCHOOL === $bikeRideType->getRegistration();
        $bikeRideTypeDto->isRegistrable = BikeRideType::REGISTRATION_NONE !== $bikeRideType->getRegistration();
        $bikeRideTypeDto->isNeedFramers = $bikeRideType->isNeedFramers();
        

        return $bikeRideTypeDto;
    }
}
