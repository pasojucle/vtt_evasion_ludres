<?php

declare(strict_types=1);

namespace App\Doctrine\DBAL\Type;

use App\Entity\Enum\OrderLineStateEnum;

class OrderLineStateEnumType extends EnumType
{
    protected function getEnum(): string
    {
        return OrderLineStateEnum::class;
    }

    public function getName(): string
    {
        return 'OrderLineState';
    }
}
