<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum LevelType: string implements TranslatableInterface
{
    case NONE = 'none';

    case ADULT = 'adult';

    case SCHOOL = 'school';

    case FRAME = 'frame';

    use EnumTrait;

    public function getIcon(): string
    {
        return match ($this) {
            self::SCHOOL => 'lucide:graduation-cap',
            self::FRAME => 'lucide:shield-user',
            self::ADULT => 'lucide:circle-user-round',
            default => 'lucide:user-round-group'
        };
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('level.type.' . $this->value, locale: $locale);
    }
}
