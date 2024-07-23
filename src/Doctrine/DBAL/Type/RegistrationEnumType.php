<?php

declare(strict_types=1);

namespace App\Doctrine\DBAL\Type;

use App\Entity\Enum\RegistrationEnum;

class RegistrationEnumType extends EnumType
{
    protected function getEnum(): string
    {
        return RegistrationEnum::class;
    }

    public function getName(): string
    {
        return 'resolution_enum';
    }
}
