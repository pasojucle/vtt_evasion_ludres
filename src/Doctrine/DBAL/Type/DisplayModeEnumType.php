<?php

declare(strict_types=1);

namespace App\Doctrine\DBAL\Type;

use App\Entity\Enum\DisplayModeEnum;

class DisplayModeEnumType extends EnumType
{
    protected function getEnum(): string
    {
        return DisplayModeEnum::class;
    }

    public function getName(): string
    {
        return 'DisplayMode';
    }
}
