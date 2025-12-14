<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum KinshipEnum: string implements TranslatableInterface
{
    case KINSHIP_FATHER = 'father';

    case KINSHIP_MOTHER = 'mother';

    case KINSHIP_GUARDIANSHIP = 'guardianship';

    case KINSHIP_OTHER = 'other';

    use EnumTrait;

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('identity.kinship.' . $this->value, locale: $locale);
    }
}
