<?php

declare(strict_types=1);

namespace App\Doctrine\DBAL\Type;

use App\Entity\Enum\LicenceStateEnum;

class LicenceStateEnumType extends EnumType
{
    protected function getEnum(): string
    {
        return LicenceStateEnum::class;
    }

    public function getName(): string
    {
        return 'LicenceState';
    }
}
