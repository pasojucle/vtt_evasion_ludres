<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum LicenceOptionEnum: string implements TranslatableInterface
{
    case FLAT_DAILY_ALLOWANCE = 'flat_daily_allowance';

    case DEATH_DISABILITY_SUPPLEMENT = 'death_disability_supplement';

    case NO_ADDITIONAL_OPTION = 'no_additional_option';

    use EnumTrait;


    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('licence.option.' . $this->value, locale: $locale);
    }
}
