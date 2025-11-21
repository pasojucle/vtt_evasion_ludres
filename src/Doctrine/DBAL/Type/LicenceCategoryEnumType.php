<?php

declare(strict_types=1);

namespace App\Doctrine\DBAL\Type;

use App\Entity\Enum\LicenceCategoryEnum;

class LicenceCategoryEnumType extends EnumType
{
    protected function getEnum(): string
    {
        return LicenceCategoryEnum::class;
    }

    public function getName(): string
    {
        return 'LicenceCategory';
    }
}
