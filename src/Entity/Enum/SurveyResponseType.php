<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum SurveyResponseType: string implements TranslatableInterface
{
    case TEXT = 'text';

    case CHOICE = 'choice';

    case CHECK = 'check';

    use EnumTrait;

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('survey.issue.' . $this->value, locale: $locale);
    }
}
