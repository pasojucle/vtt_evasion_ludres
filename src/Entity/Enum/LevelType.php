<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum LevelType: string implements TranslatableInterface
{
    case SCHOOL = 'school';

    case FRAME = 'frame';

    case ADULT = 'adult';

    use EnumTrait;

    public function getIcon(): string
    {
        return match ($this) {
            self::SCHOOL  => 'lucide:graduation-cap',
            self::FRAME => 'lucide:shield-user',
            default => 'lucide:circle-user-round'
        };
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('level.type.' . $this->value, locale: $locale);
    }
}
