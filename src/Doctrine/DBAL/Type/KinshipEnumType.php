<?php

declare(strict_types=1);

namespace App\Doctrine\DBAL\Type;

use App\Entity\Enum\KinshipEnum;

class KinshipEnumType extends EnumType
{
    protected function getEnum(): string
    {
        return KinshipEnum::class;
    }

    public function getName(): string
    {
        return 'Kinship';
    }
}
