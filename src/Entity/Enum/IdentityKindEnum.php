<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum IdentityKindEnum: string implements TranslatableInterface
{
    case MEMBER = 'member';

    case KINSHIP = 'kinship';

    case SECOND_CONTACT = 'second_contact';

    use EnumTrait;

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('identity.kind.' . $this->value, locale: $locale);
    }
}
