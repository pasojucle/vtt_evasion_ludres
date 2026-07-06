<?php

declare(strict_types=1);

namespace App\Dto\Enum;

use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum SoftDeleteStatus: string implements TranslatableInterface
{
    case ACTIVE = 'active';

    case DELETED = 'deleted';

    use EnumTrait;


    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('softDeleteStatus.' . $this->value, locale: $locale);
    }
}
