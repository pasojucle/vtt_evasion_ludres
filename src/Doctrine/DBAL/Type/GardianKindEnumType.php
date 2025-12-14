<?php

declare(strict_types=1);

namespace App\Doctrine\DBAL\Type;

use App\Entity\Enum\GardianKindEnum;

class GardianKindEnumType extends EnumType
{
    protected function getEnum(): string
    {
        return GardianKindEnum::class;
    }

    public function getName(): string
    {
        return 'GardianKind';
    }
}
