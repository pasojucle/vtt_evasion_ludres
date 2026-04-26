<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Dto\Enum\ColorVariant;
use App\Entity\Enum\BadgeTrait;
use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum PracticeEnum: string implements TranslatableInterface
{
    case VTT = 'vtt';
    case VTT_AE = 'vttae';
    case ROADBIKE = 'roadbike';
    case GRAVEL = 'gravel';
    case GRAVEL_AE = 'gravelae';
    case WALKING = 'walking';
    case NONE = 'none';

    use EnumTrait;
    use BadgeTrait;

    public function variant(): ColorVariant
    {
        return match ($this) {
            self::VTT_AE => ColorVariant::SKI,
            self::ROADBIKE => ColorVariant::PINK,
            self::GRAVEL => ColorVariant::AMBER,
            self::GRAVEL_AE => ColorVariant::INDIGO,
            self::WALKING => ColorVariant::YELLOW,
            default => ColorVariant::SUCCESS
        };
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('session.activity.' . $this->value, locale: $locale);
    }
}
