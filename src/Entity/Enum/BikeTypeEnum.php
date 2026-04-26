<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Dto\Enum\ColorVariant;
use App\Entity\Enum\BadgeTrait;
use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum BikeTypeEnum: string implements TranslatableInterface
{
    case MUSCULAR = 'muscular';
    case ELECTRIC = 'electric';
    case NONE = 'none';

    use EnumTrait;
    use BadgeTrait;

    public function variant(): ColorVariant
    {
        return match ($this) {
            self::ELECTRIC => ColorVariant::SKI,
            self::MUSCULAR => ColorVariant::PINK,
            default => ColorVariant::SUCCESS,
        };
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('bike_type.' . $this->value, locale: $locale);
    }
}
