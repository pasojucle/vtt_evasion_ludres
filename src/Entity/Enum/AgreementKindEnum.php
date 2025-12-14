<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum AgreementKindEnum: string implements TranslatableInterface
{
    case AUTHORIZATION = 'authorization';

    case CONSENT = 'consent';

    use EnumTrait;


    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('agreement.kind.' . $this->value, locale: $locale);
    }
}
