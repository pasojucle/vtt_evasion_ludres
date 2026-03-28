<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Entity\Enum\BadgeTrait;
use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum EvaluationEnum: string implements TranslatableInterface
{
    case UNACQUIRED = 'unacquired';

    case PENDING = 'pending';

    case ACQUIRED = 'acquired';

    use EnumTrait;
    use BadgeTrait;


    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('user_skill.' . $this->value, locale: $locale);
    }

    public function color(): string
    {
        return match ($this) {
            self::ACQUIRED => 'success-color',
            self::PENDING => 'primary-color',
            default => 'danger-color'
        };
    }
}
