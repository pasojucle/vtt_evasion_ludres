<?php

declare(strict_types=1);

namespace App\Dto\Enum;

use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum ActivityPeriod: string implements TranslatableInterface
{
    case MONTH = 'month';

    case UPCOMING = 'upcoming';

    case ALL = 'all';

    use EnumTrait;


    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('activity.period.' . $this->value, locale: $locale);
    }
}
