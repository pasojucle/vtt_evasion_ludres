<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\IndemnityDto;
use App\Entity\Indemnity;
use App\Model\Currency;

class IndemnityDtoTransformer
{
    public function fromEntity(Indemnity $indemnity): IndemnityDto
    {
        $indemnityDto = new IndemnityDto();
        $indemnityDto->level = $indemnity->getLevel();
        $indemnityDto->bikeRideType = $indemnity->getBikeRideType();
        $amount = new Currency($indemnity->getAmount());
        $indemnityDto->amount = $amount->toString();

        return $indemnityDto;
    }


    public function fromEntities(array $indemnitiesEntities): array
    {
        $indemnities = [];
        foreach ($indemnitiesEntities as $indemnity) {
            $indemnityDto = $this->fromEntity($indemnity);
            $indemnities[$indemnityDto->level->getId()][$indemnityDto->bikeRideType->getId()] = $indemnityDto;
        }
        
        return $indemnities;
    }
}
