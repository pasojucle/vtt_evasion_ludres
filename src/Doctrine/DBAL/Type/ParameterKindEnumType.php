<?php

declare(strict_types=1);

namespace App\Doctrine\DBAL\Type;

use App\Entity\Enum\ParameterKindEnum;

class ParameterKindEnumType extends EnumType
{
    protected function getEnum(): string
    {
        return ParameterKindEnum::class;
    }

    public function getName(): string
    {
        return 'ParameterKind';
    }
}
