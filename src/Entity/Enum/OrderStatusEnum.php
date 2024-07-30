<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Entity\Enum\EnumTrait;

enum OrderStatusEnum: string
{
    case IN_PROGRESS = 'in_progress';

    case ORDERED = 'ordered';

    case VALIDED = 'valided';

    case COMPLETED = 'completed';

    case CANCELED = 'canceled';

    use EnumTrait;

    public static function getTranslatePrefix(): string
    {
        return 'order';
    }
}
