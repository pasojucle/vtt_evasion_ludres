<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Model\Currency;
use App\Dto\IndemnityDto;
use App\Entity\Indemnity;

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


    public function fromEntities(array $indemnities): array
    {
        $indemnities = [];
        if (!empty($indemnities)) {
            foreach ($indemnities as $indemnity) {
                $indemnityDto = $this->fromEntity($indemnity);
                $indemnities[$indemnityDto->level->getId()][$indemnityDto->bikeRideType->getId()] = $indemnityDto;
            }
        }
        
        return $indemnities;
    }
}