<?php

declare(strict_types=1);

namespace App\Dto\Enum;

use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum ActivityRestriction: string implements TranslatableInterface
{
    case MEMBERS = 'members';

    case AGE = 'age';

    use EnumTrait;


    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('activity.restriction.' . $this->value, locale: $locale);
    }
}
