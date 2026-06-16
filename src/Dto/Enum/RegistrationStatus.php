<?php

declare(strict_types=1);

namespace App\Dto\Enum;

use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum RegistrationStatus: string implements TranslatableInterface
{
    case TESTING_IN_PROGRESS = 'testing_in_processing';

    case TESTING_COMPLETE = 'testing_complete';

    case NEW = 'new';

    case RENEW = 'renew';

    case WAITING_RENEW = 'waiting_renew';

    case IN_PROCESSING = 'in_processing';

    case TO_REGISTER = 'to_register';


    use EnumTrait;


    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('licence.filter.' . $this->value, locale: $locale);
    }
}
