<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum AvailabilityEnum: string implements TranslatableInterface
{
    case REGISTERED = 'registered';

    case AVAILABLE = 'available';

    case UNAVAILABLE = 'unavailable';

    use EnumTrait;


    public function trans(TranslatorInterface $translator, string $locale = null): string
    {
        return $translator->trans('session.availability.' . $this->value, locale: $locale);
    }
}
