<?php

declare(strict_types=1);

namespace App\Doctrine\DBAL\Type;

use App\Entity\Enum\RegistrationFormEnum;


class RegistrationFormEnumType extends EnumType
{
    protected function getEnum(): string
    {
        return RegistrationFormEnum::class;
    }

    public function getName(): string
    {
        return 'RegistrationForm';
    }
}
