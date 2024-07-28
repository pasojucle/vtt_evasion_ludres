<?php

declare(strict_types=1);

namespace App\Doctrine\DBAL\Type;

use App\Entity\Enum\IdentityKindEnum;

class IdentityKindEnumType extends EnumType
{
    protected function getEnum(): string
    {
        return IdentityKindEnum::class;
    }

    public function getName(): string
    {
        return 'identity_kind_enum';
    }
}
