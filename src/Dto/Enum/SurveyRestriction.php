<?php

declare(strict_types=1);

namespace App\Dto\Enum;

use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum SurveyRestriction: string implements TranslatableInterface
{
    case MEMBERS = 'members';

    case Activity = 'activity';

    use EnumTrait;


    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('survey.restriction.' . $this->value, locale: $locale);
    }
}
