<?php

declare(strict_types=1);

namespace App\Dto\View;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\View\Interface\ComponentViewInterface;

readonly class BadgeView implements ComponentViewInterface
{
    public function __construct(
        public string $value,
        public ColorVariant $variant = ColorVariant::DEFAULT,
        public Size $size = Size::SM,
        public ?string $color = null,
        public ?string $toggleStatusId = null,
    ) {
    }

    public function getName(): string
    {
        return 'badge';
    }

    public function getTemplate(): string
    {
        return 'components/_badge.html.twig';
    }
}