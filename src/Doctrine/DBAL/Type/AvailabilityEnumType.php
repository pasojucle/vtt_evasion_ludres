<?php

declare(strict_types=1);

namespace App\Doctrine\DBAL\Type;

use App\Entity\Enum\AvailabilityEnum;
use App\Entity\Enum\PracticeEnum;

class AvailabilityEnumType extends EnumType
{
    protected function getEnum(): string
    {
        return AvailabilityEnum::class;
    }

    public function getName(): string
    {
        return 'availability_enum';
    }
}
