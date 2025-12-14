<?php

declare(strict_types=1);

namespace App\Doctrine\DBAL\Type;

use App\Entity\Enum\AgreementKindEnum;

class AgreementKindEnumType extends EnumType
{
    protected function getEnum(): string
    {
        return AgreementKindEnum::class;
    }

    public function getName(): string
    {
        return 'AgreementKind';
    }
}
