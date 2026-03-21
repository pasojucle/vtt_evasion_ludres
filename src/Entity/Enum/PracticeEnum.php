<?php

declare(strict_types=1);

namespace App\Entity\Enum;

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


    public function color(): string
    {
        return match ($this) {
            self::VTT_AE => '#2596be',
            self::ROADBIKE => '#76448a',
            self::GRAVEL => '#e67e22',
            self::GRAVEL_AE => '#463d6d',
            self::WALKING => '#f1c40f',
            default => '#229954'
        };
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('session.activity.' . $this->value, locale: $locale);
    }
}
