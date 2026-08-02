<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum LicenceCoverageEnum: string implements TranslatableInterface
{
    case MINI_GEAR = 'mini_gear';

    case SMALL_GEAR = 'small_gear';

    case HIGH_GEAR = 'high_gear';

    case UNDEFINED = 'undefined';


    use EnumTrait;


    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('licence.coverage.' . $this->value, locale: $locale);
    }
}
