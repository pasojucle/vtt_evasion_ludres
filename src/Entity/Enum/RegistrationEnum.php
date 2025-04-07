<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum RegistrationEnum: string implements TranslatableInterface
{
    case NONE = 'none';
    case SCHOOL = 'school';
    case CLUSTERS = 'cluster';

    use EnumTrait;

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('bike_ride_type.registration.' . $this->value, locale: $locale);
    }
}
