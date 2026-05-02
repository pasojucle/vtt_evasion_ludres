<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Dto\Enum\ColorVariant;
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
    use BadgeTrait;

    public function variant(): ColorVariant
    {
        return match ($this) {
            self::ORDERED => ColorVariant::WARNING,
            self::VALIDED => ColorVariant::SUCCESS,
            self::COMPLETED => ColorVariant::ACCENT,
            self::CANCELED => ColorVariant::DESTRUCTIVE,
            default => ColorVariant::DEFAULT
        };
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('order.status.' . $this->value, locale: $locale);
    }
}
