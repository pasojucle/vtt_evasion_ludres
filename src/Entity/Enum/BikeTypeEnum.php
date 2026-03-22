<?php

declare(strict_types=1);

namespace App\Entity\Enum;

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

    public function color(): string
    {
        return match ($this) {
            self::ELECTRIC => '#2596be',
            self::MUSCULAR => '#76448a',
            default => '#229954'
        };
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('bike_type.' . $this->value, locale: $locale);
    }
}
