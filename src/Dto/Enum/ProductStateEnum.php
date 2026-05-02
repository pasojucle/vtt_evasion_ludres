<?php

declare(strict_types=1);

namespace App\Dto\Enum;

use App\Entity\Enum\BadgeTrait;
use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum ProductStateEnum: string implements TranslatableInterface
{

    case ENABLED = 'enabled';

    case DISABLED = 'disabled';


    use EnumTrait;
    use BadgeTrait;

        public function variant(): ColorVariant
    {
        return match ($this) {
            self::DISABLED => ColorVariant::WARNING,
            self::ENABLED => ColorVariant::SUCCESS,
        };
    }


    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('product.state.' . $this->value, locale: $locale);
    }
}
