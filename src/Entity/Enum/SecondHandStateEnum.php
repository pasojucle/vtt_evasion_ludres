<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Dto\Enum\ColorVariant;
use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum SecondHandStateEnum: string implements TranslatableInterface
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case EXPIRED = 'expired';
    case DISABLED = 'disabled';
    case ARCHIVED = 'archived';

    use EnumTrait;

    public function icon(): string
    {
        return match ($this) {
            self::DRAFT => 'lucide:pencil',
            self::PUBLISHED => 'lucide:bookmark-check',
            self::EXPIRED => 'lucide:clock-fading',
            self::DISABLED => 'lucide:eye-off',
            self::ARCHIVED => 'lucide:archive-x',
        };
    }

    public function variant(): ColorVariant
    {
        return match ($this) {
            self::DRAFT, self::EXPIRED, self::DISABLED => ColorVariant::WARNING,
            self::PUBLISHED => ColorVariant::SUCCESS,
            self::ARCHIVED => ColorVariant::DESTRUCTIVE,
        };
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('second_hand.state.' . $this->value, locale: $locale);
    }
}
