<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Enum\IdentityKindEnum;
use App\Entity\Enum\OrderStatusEnum;
use App\Entity\Enum\RegistrationEnum;
use Symfony\Contracts\Translation\TranslatorInterface;

class EnumService
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    public function translate(OrderStatusEnum|IdentityKindEnum|RegistrationEnum $enum): string
    {
        return $this->translator->trans(sprintf('%s.%s', $enum::getTranslatePrefix(), $enum->value));
    }
}
