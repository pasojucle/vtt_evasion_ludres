<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum OrderLineStateEnum: string implements TranslatableInterface
{
    case IN_STOCK = 'in_stock';

    case ON_ORDER = 'on_order';

    case UNAVAILABLE = 'unavailable';

    use EnumTrait;

    public function getAppearance(): array
    {
        return match ($this) {
            self::IN_STOCK => ['class' => 'success-color', 'color' => 'text-bg-success', 'icon' => 'check', 'backgroundColor' => 'background-ligth'],
            self::ON_ORDER => ['class' => 'primary-color', 'color' => 'text-bg-info', 'icon' => 'times', 'backgroundColor' => 'background-ligth'],
            self::UNAVAILABLE => ['class' => 'danger-color', 'color' => 'text-bg-danger', 'icon' => 'ban', 'backgroundColor' => 'background-disbled'],
        };
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('order_line.state.' . $this->value, locale: $locale);
    }
}
