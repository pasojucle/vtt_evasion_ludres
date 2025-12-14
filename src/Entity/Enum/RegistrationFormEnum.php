<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum RegistrationFormEnum: string implements TranslatableInterface
{
    case NONE = 'none';

    case REGISTRATION_DOCUMENT = 'registration_document';

    case HEALTH_QUESTION = 'health_question';

    case IDENTITY = 'identity';

    case HEALTH = 'health';

    case LICENCE_AGREEMENTS = 'licence_agreements';

    case LICENCE_COVERAGE = 'licence_coverage';

    case MEMBERSHIP_FEE = 'membership_fee';

    case REGISTRATION_FILE = 'registration_file';

    case OVERVIEW = 'overview';

    case MEMBER = 'member';

    case GARDIANS = 'gardians';

    use EnumTrait;

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('form.' . $this->value, locale: $locale);
    }
}
