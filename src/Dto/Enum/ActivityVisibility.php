<?php

declare(strict_types=1);

namespace App\Dto\Enum;

use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum ActivityVisibility: string implements TranslatableInterface
{
    case PRIVATE = 'private';

    case PUBLIC = 'public';


    use EnumTrait;


    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('activity.visibility.' . $this->value, locale: $locale);
    }
}
