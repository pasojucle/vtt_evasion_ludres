<?php

declare(strict_types=1);

namespace App\Doctrine\DBAL\Type;

use App\Entity\Enum\OrderStatusEnum;

class OrderStatusEnumType extends EnumType
{
    protected function getEnum(): string
    {
        return OrderStatusEnum::class;
    }

    public function getName(): string
    {
        return 'OrderStatus';
    }
}
