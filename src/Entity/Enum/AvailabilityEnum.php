<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Dto\Enum\ColorVariant;
use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum AvailabilityEnum: string implements TranslatableInterface
{
    case REGISTERED = 'registered';

    case AVAILABLE = 'available';

    case UNAVAILABLE = 'unavailable';

    case NONE = 'none';

    use EnumTrait;
    use BadgeTrait;

    public function variant(): ColorVariant
    {
        return match ($this) {
            self::REGISTERED => ColorVariant::SUCCESS,
            self::AVAILABLE => ColorVariant::WARNING,
            self::UNAVAILABLE => ColorVariant::DESTRUCTIVE,
            default => ColorVariant::DEFAULT
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::REGISTERED => 'lucide:user-check',
            self::AVAILABLE => 'lucide:user-plus',
            self::UNAVAILABLE => 'lucide:user-x',
            default => 'lucide:badge-question-mark'
        };
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('session.availability.' . $this->value, locale: $locale);
    }
}
