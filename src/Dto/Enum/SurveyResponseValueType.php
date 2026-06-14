<?php

declare(strict_types=1);

namespace App\Dto\Enum;

use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum SurveyResponseValueType: string implements TranslatableInterface
{
    case TEXT = 'text';

    case YES = 'yes';

    case NO = 'no';

    case NO_OPINION = 'no_opinion';

    use EnumTrait;


    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('survey.response.' . $this->value, locale: $locale);
    }
}
