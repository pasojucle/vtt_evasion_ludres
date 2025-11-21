<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum LicenceCategoryEnum: string implements TranslatableInterface
{
    case SCHOOL = 'school';

    case ADULT = 'adult';

    case SCHOOL_AND_ADULT = 'school_and_adult';

    use EnumTrait;


    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('licence.category.' . $this->value, locale: $locale);
    }
}
