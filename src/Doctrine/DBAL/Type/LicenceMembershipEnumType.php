<?php

declare(strict_types=1);

namespace App\Doctrine\DBAL\Type;

use App\Entity\Enum\LicenceMembershipEnum;

class LicenceMembershipEnumType extends EnumType
{
    protected function getEnum(): string
    {
        return LicenceMembershipEnum::class;
    }

    public function getName(): string
    {
        return 'LicenceMembership';
    }
}
