<?php

declare(strict_types=1);

namespace App\Doctrine\DBAL\Type;

use App\Entity\Enum\PermissionEnum;

class PermissionEnumType extends EnumType
{
    protected function getEnum(): string
    {
        return PermissionEnum::class;
    }

    public function getName(): string
    {
        return 'Permission';
    }
}
