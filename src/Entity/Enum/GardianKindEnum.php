<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum GardianKindEnum: string implements TranslatableInterface
{
    case LEGAL_GARDIAN = 'legal_gardian';

    case SECOND_CONTACT = 'second_contact';

    use EnumTrait;

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('gardian.kind.' . $this->value, locale: $locale);
    }
}
