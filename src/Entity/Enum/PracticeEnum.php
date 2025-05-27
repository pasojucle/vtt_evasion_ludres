<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum PracticeEnum: string implements TranslatableInterface
{
    case VTT = 'vtt';
    case VTTAE = 'vttae';
    case ROADBIKE = 'roadbike';
    case GRAVEL = 'gravel';
    case WALKING = 'walking';

    use EnumTrait;


    public function color(): string
    {
        return match ($this) {
            self::VTTAE => '#2596be',
            self::ROADBIKE => '#76448a',
            self::GRAVEL => '#e67e22',
            self::WALKING => '#f1c40f',
            default => '#229954'
        };
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('session.activity.' . $this->value, locale: $locale);
    }
}
