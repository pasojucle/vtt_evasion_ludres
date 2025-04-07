<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum OrderStatusEnum: string implements TranslatableInterface
{
    case IN_PROGRESS = 'in_progress';

    case ORDERED = 'ordered';

    case VALIDED = 'valided';

    case COMPLETED = 'completed';

    case CANCELED = 'canceled';

    use EnumTrait;

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('order.filter.' . $this->value, locale: $locale);
    }
}
