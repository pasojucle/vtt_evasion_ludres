<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum ParameterKindEnum: string implements TranslatableInterface
{
    case CHOICE = 'choice';

    case BOOL = 'bool';

    case TEXT = 'text';

    case IMAGE = 'image';

    use EnumTrait;


    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('parameter.kind.' . $this->value, locale: $locale);
    }
}
