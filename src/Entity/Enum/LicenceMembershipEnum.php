<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum LicenceMembershipEnum: string implements TranslatableInterface
{
    case TRIAL = 'trial';

    case YEARLY = 'yearly';

    case TRIAL_AND_YEARLY = 'trial_and_yearly';

    use EnumTrait;

    public function icon(): string
    {
        if (self::YEARLY === $this) {
            return 'lucide:calendar-check';
        }

        return 'lucide:calendar-clock';
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('licence.membership.' . $this->value, locale: $locale);
    }
}
