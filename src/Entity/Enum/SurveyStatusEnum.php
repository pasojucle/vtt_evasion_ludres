<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Dto\Enum\ColorVariant;
use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum SurveyStatusEnum: string implements TranslatableInterface
{
    case PENDING = 'pending';

    case EXPIRED = 'expired';

    case DISABLED = 'disabled';

    use EnumTrait;
    use BadgeTrait;

    public function variant(): ColorVariant
    {
        return match ($this) {
            self::PENDING => ColorVariant::SUCCESS,
            self::EXPIRED => ColorVariant::ACCENT,
            self::DISABLED => ColorVariant::DESTRUCTIVE,
        };
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('survey.filter.' . $this->value, locale: $locale);
    }
}
