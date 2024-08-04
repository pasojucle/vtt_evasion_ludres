<?php

declare(strict_types=1);

namespace App\Doctrine\DBAL\Type;

use App\Entity\Enum\PracticeEnum;

class PracticeEnumType extends EnumType
{
    protected function getEnum(): string
    {
        return PracticeEnum::class;
    }

    public function getName(): string
    {
        return 'practice_enum';
    }
}
